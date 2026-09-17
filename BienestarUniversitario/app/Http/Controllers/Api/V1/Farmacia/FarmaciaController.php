<?php

namespace App\Http\Controllers\Api\V1\Farmacia;

use App\Http\Controllers\Controller;
use App\Models\ProductoFarmacia;
use App\Models\StockHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FarmaciaController extends Controller
{
    /**
     * Listar todos los productos de farmacia (Inventario)
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductoFarmacia::with('presentacion');

        // Filtro por estado activo/inactivo
        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        // Búsqueda por nombre o código
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        // Filtro por presentación
        if ($request->has('id_presentacion')) {
            $query->where('id_presentacion', $request->id_presentacion);
        }

        // Filtro por stock bajo
        if ($request->boolean('low_stock')) {
            $umbral = $request->get('umbral', 10);
            $query->where(function ($q) use ($umbral) {
                $q->where('stock_cajas', '<=', $umbral)
                  ->orWhere('stock_unidades', '<=', $umbral);
            });
        }

        $productos = $query->orderBy('nombre')->get();

        return response()->json([
            'data' => $productos,
            'total_productos' => $productos->count(),
            'total_cajas' => $productos->sum('stock_cajas'),
            'total_unidades' => $productos->sum('stock_unidades'),
            'message' => 'Inventario de farmacia'
        ]);
    }

    /**
     * Mostrar un producto
     */
    public function show(int $id): JsonResponse
    {
        $producto = ProductoFarmacia::with(['presentacion', 'historial' => function ($query) {
            $query->with('usuario')->latest()->limit(20);
        }])->find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json([
            'data' => $producto,
            'message' => 'Producto retrieved'
        ]);
    }

    /**
     * Crear un producto (Enfermera)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:productos_farmacia,codigo',
            'nombre' => 'required|string|max:255',
            'id_presentacion' => 'required|integer|exists:presentaciones,id',
            'stock_inicial_cajas' => 'required|integer|min:0',
            'stock_inicial_unidades' => 'required|integer|min:0',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        $producto = ProductoFarmacia::create([
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'id_presentacion' => $request->id_presentacion,
            'stock_cajas' => $request->stock_inicial_cajas,
            'stock_unidades' => $request->stock_inicial_unidades,
        ]);

        // Registrar movimiento inicial si hay stock
        if ($request->stock_inicial_cajas > 0 || $request->stock_inicial_unidades > 0) {
            StockHistory::create([
                'id_producto_farmacia' => $producto->id,
                'id_usuario' => $user->id,
                'tipo_movimiento' => 'entrada',
                'cantidad_cajas' => $request->stock_inicial_cajas,
                'cantidad_unidades' => $request->stock_inicial_unidades,
                'stock_anterior_cajas' => 0,
                'stock_anterior_unidades' => 0,
                'stock_nuevo_cajas' => $request->stock_inicial_cajas,
                'stock_nuevo_unidades' => $request->stock_inicial_unidades,
                'descripcion' => $request->descripcion ?? 'Stock inicial',
            ]);
        }

        return response()->json([
            'data' => $producto->load('presentacion'),
            'message' => 'Producto creado exitosamente'
        ], 201);
    }

    /**
     * Actualizar datos del producto (no stock)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $producto = ProductoFarmacia::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $request->validate([
            'codigo' => 'sometimes|required|string|max:50|unique:productos_farmacia,codigo,' . $id,
            'nombre' => 'sometimes|required|string|max:255',
            'id_presentacion' => 'sometimes|required|integer|exists:presentaciones,id',
            'activo' => 'sometimes|boolean',
        ]);

        $producto->update($request->only(['codigo', 'nombre', 'id_presentacion', 'activo']));

        return response()->json([
            'data' => $producto->load('presentacion'),
            'message' => 'Producto actualizado'
        ]);
    }

    /**
     * Deshabilitar un producto (no eliminar)
     */
    public function disable(int $id): JsonResponse
    {
        $producto = ProductoFarmacia::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $producto->update(['activo' => false]);

        return response()->json([
            'data' => $producto,
            'message' => 'Producto deshabilitado'
        ]);
    }

    /**
     * Habilitar un producto
     */
    public function enable(int $id): JsonResponse
    {
        $producto = ProductoFarmacia::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $producto->update(['activo' => true]);

        return response()->json([
            'data' => $producto,
            'message' => 'Producto habilitado'
        ]);
    }

    /**
     * Agregar stock (suma) - Enfermera
     */
    public function addStock(Request $request, int $id): JsonResponse
    {
        $producto = ProductoFarmacia::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $request->validate([
            'cantidad_cajas' => 'required|integer|min:1',
            'cantidad_unidades' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $historial = $producto->agregarStock(
            $request->cantidad_cajas,
            $request->cantidad_unidades,
            $user,
            $request->descripcion
        );

        return response()->json([
            'data' => [
                'producto' => $producto->fresh('presentacion'),
                'movimiento' => $historial,
            ],
            'message' => 'Stock agregado exitosamente'
        ]);
    }

    /**
     * Reducir stock (resta) - Enfermera
     */
    public function subtractStock(Request $request, int $id): JsonResponse
    {
        $producto = ProductoFarmacia::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $request->validate([
            'cantidad_cajas' => 'required|integer|min:1',
            'cantidad_unidades' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:500',
        ]);

        // Verificar stock suficiente
        if (!$producto->tieneStock($request->cantidad_cajas, $request->cantidad_unidades)) {
            return response()->json([
                'message' => 'Stock insuficiente',
                'stock_actual' => [
                    'cajas' => $producto->stock_cajas,
                    'unidades' => $producto->stock_unidades,
                ]
            ], 400);
        }

        $user = Auth::user();
        $historial = $producto->reducirStock(
            $request->cantidad_cajas,
            $request->cantidad_unidades,
            $user,
            $request->descripcion
        );

        return response()->json([
            'data' => [
                'producto' => $producto->fresh('presentacion'),
                'movimiento' => $historial,
            ],
            'message' => 'Stock reducido exitosamente'
        ]);
    }

    /**
     * Ver historial de movimientos de un producto
     */
    public function stockHistory(int $id): JsonResponse
    {
        $producto = ProductoFarmacia::find($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $historial = StockHistory::with('usuario')
            ->where('id_producto_farmacia', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $historial,
            'message' => 'Historial de movimientos'
        ]);
    }

    /**
     * Buscar productos por nombre o código (para autocompletado)
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $query = $request->query('query');

        $productos = ProductoFarmacia::with('presentacion')
            ->where('activo', true)
            ->where(function ($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('codigo', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $productos,
            'message' => 'Resultados de búsqueda'
        ]);
    }

    /**
     * Verificar stock disponible
     */
    public function checkStock(Request $request): JsonResponse
    {
        $request->validate([
            'id_producto' => 'required|integer|exists:productos_farmacia,id',
            'cantidad_cajas' => 'required|integer|min:0',
            'cantidad_unidades' => 'required|integer|min:0',
        ]);

        $producto = ProductoFarmacia::find($request->id_producto);

        $disponible = $producto->tieneStock(
            $request->cantidad_cajas,
            $request->cantidad_unidades
        );

        return response()->json([
            'data' => [
                'disponible' => $disponible,
                'stock_actual_cajas' => $producto->stock_cajas,
                'stock_actual_unidades' => $producto->stock_unidades,
            ],
            'message' => $disponible ? 'Stock disponible' : 'Stock insuficiente'
        ]);
    }

    /**
     * Productos con stock bajo (para alertas)
     */
    public function lowStock(Request $request): JsonResponse
    {
        $umbral = $request->get('umbral', 10);

        $productos = ProductoFarmacia::with('presentacion')
            ->where('activo', true)
            ->where(function ($query) use ($umbral) {
                $query->where('stock_cajas', '<=', $umbral)
                      ->orWhere('stock_unidades', '<=', $umbral);
            })
            ->orderBy('stock_cajas')
            ->get();

        return response()->json([
            'data' => $productos,
            'total_productos_bajo_stock' => $productos->count(),
            'message' => 'Productos con stock bajo'
        ]);
    }

    /**
     * Reporte de inventario completo
     */
    public function inventoryReport(): JsonResponse
    {
        $productos = ProductoFarmacia::with('presentacion')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $reporte = [
            'resumen' => [
                'total_productos' => $productos->count(),
                'total_cajas' => $productos->sum('stock_cajas'),
                'total_unidades' => $productos->sum('stock_unidades'),
                'productos_stock_bajo' => $productos->filter(function ($p) {
                    return $p->stock_cajas <= 10 || $p->stock_unidades <= 10;
                })->count(),
            ],
            'por_presentacion' => $productos->groupBy('presentacion.nombre')->map(function ($items, $nombre) {
                return [
                    'presentacion' => $nombre,
                    'cantidad_productos' => $items->count(),
                    'total_cajas' => $items->sum('stock_cajas'),
                    'total_unidades' => $items->sum('stock_unidades'),
                ];
            })->values(),
            'productos' => $productos,
        ];

        return response()->json([
            'data' => $reporte,
            'message' => 'Reporte de inventario'
        ]);
    }
}