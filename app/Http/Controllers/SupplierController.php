<?php

namespace App\Http\Controllers;

use App\Models\SupplierModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Supplier',
            'list' => ['Home', 'Supplier']
        ];

        $page = (object) [
            'title' => 'Supplier dalam sistem'
        ];

        $activeMenu = 'supplier';
        
        return view('supplier.index', compact('breadcrumb', 'page', 'activeMenu'));
    }

    public function list()
    {
        $supplier = SupplierModel::select('supplier_id', 'supplier_kode', 'supplier_nama', 'supplier_alamat');

        return DataTables::of($supplier)
            ->addIndexColumn()
            ->addColumn('aksi', function ($supplier) {
                return '<a href="'.url('/supplier/'.$supplier->supplier_id.'/edit').'" class="btn btn-warning btn-sm">Edit</a> '
                    .'<form class="d-inline-block" method="POST" action="'.url('/supplier/'.$supplier->supplier_id).'">'
                    .csrf_field().method_field('DELETE')
                    .'<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Yakin hapus data?\');">Hapus</button></form>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create()
    {
        $breadcrumb = (object) [
            'title' => 'Tambah Supplier',
            'list' => ['Home', 'Supplier', 'Tambah']
        ];

        $page = (object) [
            'title' => 'Tambah supplier baru'
        ];

        $activeMenu = 'supplier';
        
        return view('supplier.create', compact('breadcrumb', 'page', 'activeMenu'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_kode' => 'required|unique:m_supplier,supplier_kode',
            'supplier_nama' => 'required|string|max:255',
            'supplier_alamat' => 'nullable|string'
        ]);

        SupplierModel::create($request->only(['supplier_kode', 'supplier_nama', 'supplier_alamat']));

        return redirect('/supplier')->with('success', 'Supplier berhasil ditambahkan');
    }

    public function edit($id)
    {
        $supplier = SupplierModel::findOrFail($id);

        $breadcrumb = (object) [
            'title' => 'Edit Supplier',
            'list' => ['Home', 'Supplier', 'Edit']
        ];

        $page = (object) [
            'title' => 'Edit supplier'
        ];

        $activeMenu = 'supplier';
        
        return view('supplier.edit', compact('breadcrumb', 'page', 'supplier', 'activeMenu'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_kode' => 'required|unique:m_supplier,supplier_kode,'.$id.',supplier_id',
            'supplier_nama' => 'required|string|max:255',
            'supplier_alamat' => 'nullable|string'
        ]);

        $supplier = SupplierModel::findOrFail($id);
        $supplier->update($request->only(['supplier_kode', 'supplier_nama', 'supplier_alamat']));

        return redirect('/supplier')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy($id)
    {
        SupplierModel::destroy($id);
        return redirect('/supplier')->with('success', 'Supplier berhasil dihapus');
    }
}
