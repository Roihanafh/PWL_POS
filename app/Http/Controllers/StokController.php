<?php

namespace App\Http\Controllers;

use App\Models\StokModel;
use App\Models\SupplierModel;
use App\Models\BarangModel;
use App\Models\KategoriModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StokController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Stok Barang',
            'list' => ['Home', 'Stok']
        ];

        $page = (object) [
            'title' => 'Stok Barang dalam sistem'
        ];

        $activeMenu = 'stok';
        
        return view('stok.index', compact('breadcrumb', 'page', 'activeMenu'));
    }

    public function list()
    {
        $stok = StokModel::join('m_supplier', 't_stok.supplier_id', '=', 'm_supplier.supplier_id')
            ->join('m_barang', 't_stok.barang_id', '=', 'm_barang.barang_id')
            ->join('m_user', 't_stok.user_id', '=', 'm_user.user_id')
            ->select(
                't_stok.stok_id',
                'm_supplier.supplier_nama as supplier',
                'm_barang.barang_nama as barang',
                'm_user.username as user',
                't_stok.stok_tanggal',
                't_stok.stok_jumlah'
            );

        return DataTables::of($stok)
            ->addIndexColumn()
            ->addColumn('aksi', function ($stok) {
                // return '<a href="'.url('/stok/'.$stok->stok_id.'/edit').'" class="btn btn-warning btn-sm">Edit</a> '
                //     .'<form class="d-inline-block" method="POST" action="'.url('/stok/'.$stok->stok_id).'">'
                //     .csrf_field().method_field('DELETE')
                //     .'<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Yakin hapus data?\');">Hapus</button></form>';
                $btn = '<button onclick="modalAction(\''.url('/stok/' . $stok->stok_id . '/show_ajax').'\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/stok/' . $stok->stok_id . '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/stok/' . $stok->stok_id . '/delete_ajax').'\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create()
    {
        $breadcrumb = (object) [
            'title' => 'Tambah Stok',
            'list' => ['Home', 'Stok Barang', 'Tambah']
        ];

        $page = (object) [
            'title' => 'Tambah stok baru'
        ];

        $supplier = SupplierModel::select('supplier_id', 'supplier_nama')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama')->get();
        $user = UserModel::select('user_id', 'username')->get();

        $activeMenu = 'stok';
        
        return view('stok.create', compact('breadcrumb', 'page', 'supplier', 'barang', 'user', 'activeMenu'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:m_supplier,supplier_id',
            'barang_id' => 'required|exists:m_barang,barang_id',
            'user_id' => 'required|exists:m_user,user_id',
            'stok_tanggal' => now(),
            'stok_jumlah' => 'required|integer|min:1'
        ]);

        StokModel::create($request->only(['supplier_id', 'barang_id', 'user_id', 'stok_tanggal', 'stok_jumlah']));

        return redirect('/stok')->with('success', 'Stok berhasil ditambahkan');
    }

    public function show($id)
    {
        $stok = StokModel::join('m_supplier', 't_stok.supplier_id', '=', 'm_supplier.supplier_id')
            ->join('m_barang', 't_stok.barang_id', '=', 'm_barang.barang_id')
            ->join('m_user', 't_stok.user_id', '=', 'm_user.user_id')
            ->where('t_stok.stok_id', $id)
            ->select(
                't_stok.*',
                'm_supplier.supplier_nama as supplier',
                'm_barang.barang_nama as barang',
                'm_user.username as user'
            )->firstOrFail();

        $breadcrumb = (object) [
            'title' => 'Detail Stok',
            'list' => ['Home', 'Stok', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail stok'
        ];

        $activeMenu = 'stok';
        
        return view('stok.show', compact('breadcrumb', 'page', 'stok', 'activeMenu'));
    }

    public function edit($id)
    {
        $stok = StokModel::findOrFail($id);

        $breadcrumb = (object) [
            'title' => 'Edit Stok',
            'list' => ['Home', 'Stok', 'Edit']
        ];

        $page = (object) [
            'title' => 'Edit stok'
        ];

        $suppliers = SupplierModel::select('supplier_id', 'supplier_nama')->get();
        $barangs = BarangModel::select('barang_id', 'barang_nama')->get();
        $users = UserModel::select('user_id', 'username')->get();

        $activeMenu = 'stok';
        
        return view('stok.edit', compact('breadcrumb', 'page', 'stok', 'suppliers', 'barangs', 'users', 'activeMenu'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'stok_jumlah' => 'required|integer|min:1',
        ]);

        $stok = StokModel::findOrFail($id);
        $stok->update([
            'stok_jumlah' => $request->stok_jumlah,
            'stok_tanggal' => now(),
        ]);

        return redirect('/stok')->with('success', 'Stok berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $stok = StokModel::find($id);
        if (!$stok) {
            return redirect('/stok')->with('error', 'Data stok tidak ditemukan');
        }

        try {
            StokModel::destroy($id);
            return redirect('/stok')->with('success', 'Data stok berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/stok')->with('error', 'Data stok gagal dihapus karena masih terkait dengan data lain');
        }
    }
    public function create_ajax()
{
    $supplier = SupplierModel::select('supplier_id', 'supplier_nama')->get();
    $barang = BarangModel::select('barang_id', 'barang_nama')->get();
    $user = UserModel::select('user_id', 'username')->get();
    
    return view('stok.create_ajax', compact('supplier', 'barang','user'));
}

public function store_ajax(Request $request){
    if ($request->ajax() || $request->wantsJson()) {
        $rules = [
            'supplier_id' => 'required|integer',
            'barang_id' => 'required|integer',
            'user_id' => 'required|integer',
            'stok_tanggal' => 'required|date',
            'stok_jumlah' => 'required|integer|min:1',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'msgField' => $validator->errors(),
            ]);
        }
        try {
            StokModel::create($request->all());
            return response()->json(['status' => true, 'message' => 'Data stok berhasil disimpan']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    return redirect('/');
}

public function edit_ajax(string $id)
{
    $stok = StokModel::find($id);
    $supplier = SupplierModel::all();
    $barang = BarangModel::all();
    $user = UserModel::select('user_id', 'username')->get();

    return view('stok.edit_ajax', compact('stok', 'supplier', 'barang','user'));
}

public function update_ajax(Request $request, $id)
{
    if ($request->ajax() || $request->wantsJson()) {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|integer',
            'barang_id' => 'required|integer',
            'stok_tanggal' => 'required|date',
            'stok_jumlah' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi Gagal',
                'msgField' => $validator->errors(),
            ]);
        }

        $stok = StokModel::find($id);
        $stok->update($request->all());
        return response()->json(['status' => true, 'message' => 'Data stok berhasil diupdate']);
    }

    return redirect('/');
}

public function confirm_ajax(string $id)
{
    $stok = StokModel::find($id);
    return view('stok.confirm_ajax', compact('stok'));
}

public function delete_ajax(Request $request, $id)
{
    try {
        $stok = StokModel::find($id);
        if ($stok) {
            $stok->delete();
            return response()->json([
                'status' => true,
                'message' => 'Data stok berhasil dihapus'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    } catch (\Illuminate\Database\QueryException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Data gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini'
        ]);
    }
    return redirect('/');
}

    // public function destroy($id)
    // {
    //     StokModel::destroy($id);
    //     return redirect('/stok')->with('success', 'Stok berhasil dihapus');
    // }
}
