<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('name')->get(['id', 'name']);
        $acciones = ['CREATED', 'UPDATED', 'DELETED', 'LOGIN', 'LOGOUT', 'CUSTOM'];
        $modelos = ActivityLog::select('model_type')
            ->whereNotNull('model_type')
            ->distinct()
            ->orderBy('model_type')
            ->pluck('model_type');

        return view('admin.activity.index', compact('usuarios', 'acciones', 'modelos'));
    }

    public function data(Request $r)
    {
        $q = ActivityLog::query()->with('user:id,name');

        if ($r->filled('user_id')) {
            $q->where('user_id', $r->user_id);
        }
        if ($r->filled('action')) {
            $q->where('action', $r->action);
        }
        if ($r->filled('model_type')) {
            $q->where('model_type', $r->model_type);
        }
        if ($r->filled('model_id')) {
            $q->where('model_id', $r->model_id);
        }

        if ($r->filled('from')) {
            $q->where('created_at', '>=', $r->from . ' 00:00:00');
        }
        if ($r->filled('to')) {
            $q->where('created_at', '<=', $r->to . ' 23:59:59');
        }

        if ($r->filled('term')) {
            $term = '%' . $r->term . '%';
            $q->where(function ($w) use ($term) {
                $w->where('description', 'like', $term)
                    ->orWhere('url', 'like', $term)
                    ->orWhere('ip', 'like', $term)
                    ->orWhere('user_agent', 'like', $term);
            });
        }

        return DataTables::of($q)
            ->addIndexColumn()
            // Enviamos ISO8601 para que el frontend lo muestre en zona horaria local del equipo cliente.
            ->editColumn('created_at', fn($row) => $row->created_at?->toIso8601String())
            ->addColumn('usuario', fn($row) => $row->user?->name ?? '-')
            ->addColumn('modelo', fn($row) => class_basename($row->model_type) ?: '-')
            ->addColumn('resumen', function ($row) {
                if ($row->action === 'UPDATED') {
                    $changes = array_keys(array_diff_assoc($row->after ?? [], $row->before ?? []));
                    return $changes ? 'Cambios: ' . implode(', ', array_slice($changes, 0, 5)) : '-';
                }
                return $row->description ?: '-';
            })
            ->addColumn('acciones', function ($row) {
                $show = route('actividad.show', $row->id);
                $del = route('actividad.destroy', $row->id);
                return view('admin.activity.partials.actions', compact('show', 'del'))->render();
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function show(ActivityLog $actividad)
    {
        $before = $actividad->before ?? [];
        $after = $actividad->after ?? [];
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        $diff = [];
        $uaInfo = $this->parseUserAgent((string) ($actividad->user_agent ?? ''));

        foreach ($keys as $k) {
            $diff[] = [
                'key' => $k,
                'before' => $before[$k] ?? null,
                'after' => $after[$k] ?? null,
                'changed' => ($before[$k] ?? null) !== ($after[$k] ?? null),
            ];
        }

        return view('admin.activity.show', compact('actividad', 'diff', 'uaInfo'));
    }

    public function destroy(ActivityLog $actividad)
    {
        $actividad->delete();
        return back()->with('mensaje', 'Registro eliminado')->with('icono', 'success');
    }

    public function purge(Request $r)
    {
        $days = max(1, (int) $r->input('days', 90));
        $count = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();
        return back()->with('mensaje', "Se purgaron {$count} registros anteriores a {$days} dias.")->with('icono', 'success');
    }

    private function parseUserAgent(string $ua): array
    {
        $browser = 'Desconocido';
        $version = '';
        $os = 'Desconocido';

        if (preg_match('/Windows NT 10\.0/i', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6\.3/i', $ua)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6\.1/i', $ua)) {
            $os = 'Windows 7';
        } elseif (preg_match('/Android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            $os = 'iOS';
        } elseif (preg_match('/Mac OS X|Macintosh/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        }

        $map = [
            ['pattern' => '/Edg\/([0-9\.]+)/i', 'name' => 'Microsoft Edge'],
            ['pattern' => '/OPR\/([0-9\.]+)/i', 'name' => 'Opera'],
            ['pattern' => '/Firefox\/([0-9\.]+)/i', 'name' => 'Mozilla Firefox'],
            ['pattern' => '/Chrome\/([0-9\.]+)/i', 'name' => 'Google Chrome'],
            ['pattern' => '/Version\/([0-9\.]+).*Safari/i', 'name' => 'Safari'],
        ];

        foreach ($map as $item) {
            if (preg_match($item['pattern'], $ua, $m)) {
                $browser = $item['name'];
                $version = $m[1] ?? '';
                break;
            }
        }

        $versionLabel = $version !== '' ? explode('.', $version)[0] : '';
        $label = trim($browser . ($versionLabel !== '' ? ' ' . $versionLabel : '') . ' en ' . $os);

        return [
            'browser' => $browser,
            'version' => $version,
            'os' => $os,
            'label' => $label,
            'raw' => $ua,
        ];
    }
}
