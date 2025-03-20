<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\KategoriModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BarangController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Barang',
            'list' => ['Home', 'Barang']
        ];

        $page = (object) [
            'title' => 'Barang dalam sistem'
        ];
        $kategori=KategoriModel::all();

        $activeMenu = 'barang';
        
        return view('barang.index', compact('breadcrumb', 'page','kategori', 'activeMenu'));
    }

    public function list(Request $request)
    {
        $barang = BarangModel::select(
            'barang_id',
            'barang_kode',
            'barang_nama',
            'harga_beli',
            'harga_jual',
            'kategori_id'
        )
        ->with('kategori');

        if ($request->kategori_id) {
            $barang = $barang->where('kategori_id', $request->kategori_id);    
            }

        return DataTables::of($barang)
            ->addIndexColumn()
            ->addColumn('aksi', function ($barang) {
                // return '<a href="'.url('/barang/'.$barang->barang_id).'" class="btn btn-info btn-sm">Detail</a> '
                //     .'<a href="'.url('/barang/'.$barang->barang_id.'/edit').'" class="btn btn-warning btn-sm">Edit</a> '
                //     .'<form class="d-inline-block" method="POST" action="'.url('/barang/'.$barang->barang_id).'">'
                //     .csrf_field().method_field('DELETE')
                //     .'<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Yakin hapus data?\');">Hapus</button></form>';
                $btn = '<button onclick="modalAction(\''.url('/barang/' . $barang->barang_id . '/show_ajax').'\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/barang/' . $barang->barang_id . '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/barang/' . $barang->barang_id . '/delete_ajax').'\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }



    public function create()
    {
        $kategori = KategoriModel::all(); // Ambil semua kategori untuk dropdown
        $breadcrumb = (object) [
            'title' => 'Tambah Barang',
            'list' => ['Home', 'Barang', 'Tambah']
        ];

        $page = (object) [
            'title' => 'Tambah barang baru'
        ];

        $activeMenu = 'barang';
        
        return view('barang.create', compact('breadcrumb', 'page','kategori', 'activeMenu'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barang_kode' => 'required|string|max:10|unique:m_barang,barang_kode',
            'barang_nama' => 'required|string|max:100',
            'harga_jual' => 'required|numeric',
            'harga_beli' => 'required|numeric',
            'kategori_id' => 'required|integer'
        ]);

        BarangModel::create($request->only(['barang_kode', 'barang_nama', 'harga_jual', 'harga_beli', 'kategori_id']));

        return redirect('/barang')->with('success', 'Barang berhasil ditambahkan');
    }

    public function show($id)
    {
        $barang = DB::table('m_barang')
            ->join('m_kategori', 'm_kategori.kategori_id', '=', 'm_barang.kategori_id')
            ->select(
                'm_barang.barang_id',
                'm_barang.barang_kode',
                'm_barang.barang_nama',
                'm_barang.harga_beli',
                'm_barang.harga_jual',
                'm_kategori.kategori_nama as kategori'
            )
            ->where('m_barang.barang_id', $id)
            ->first(); // atau firstOrFail()


        $breadcrumb = (object) [
            'title' => 'Detail Barang',
            'list' => ['Home', 'Barang', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail barang'
        ];

        $activeMenu = 'barang';
        
        return view('barang.show', compact('breadcrumb', 'page', 'barang', 'activeMenu'));
    }

    public function edit($id)
    {
        $barang = BarangModel::findOrFail($id);
        $kategori = KategoriModel::all(); // Ambil semua kategori untuk dropdown

        $breadcrumb = (object) [
            'title' => 'Edit Barang',
            'list' => ['Home', 'Barang', 'Edit']
        ];

        $page = (object) [
            'title' => 'Edit barang'
        ];

        $activeMenu = 'barang';
        
        return view('barang.edit', compact('breadcrumb', 'page', 'barang', 'kategori', 'activeMenu'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'barang_kode' => 'required|string|max:10|unique:m_barang,barang_kode,'.$id.',barang_id',
            'barang_nama' => 'required|string|max:100',
            'harga_jual' => 'required|numeric',
            'harga_beli' => 'required|numeric',
            'kategori_id'=> 'required|integer',
        ]);

        BarangModel::findOrFail($id)->update($request->only(['barang_kode', 'barang_nama', 'harga_jual', 'harga_beli', 'kategori_id']));

        return redirect('/barang')->with('success', 'Barang berhasil diperbarui');
    }

    public function destroy($id)
    {
        $level = BarangModel::find($id);
        if (!$level) {
            return redirect('/barang')->with('error', 'Data barang tidak ditemukan');
        }

        try {
            BarangModel::destroy($id);
            return redirect('/barang')->with('success', 'Data barang berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/barang')->with('error', 'Data barang gagal dihapus karena masih terkait dengan data lain');
        }
    }

    public function create_ajax()
    {
        $kategori = KategoriModel::select('kategori_id', 'kategori_nama')->get();
        
        return view('barang.create_ajax')
        ->with('kategori', $kategori);
    }

    public function store_ajax(Request $request){
        //cek apakah request berupa ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules=[
                'kategori_id'=> 'required|integer',
                'barang_kode' => 'required|string|max:10|unique:m_barang,barang_kode',
                'barang_nama' => 'required|string|max:100',
                'harga_jual' => 'required|numeric',
                'harga_beli' => 'required|numeric',
                ];
            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails() ) {
                return response()->json([
                'status' => false,//responser status, false error, true berhasil
                'message'=> 'Validasi gagal',
                'msgField'=>$validator->errors(), //pesan error validasi
                
                ]);
            }
            try {
                BarangModel::create($request->all());
                return response()->json(['status' => true, 'message' => 'Data berhasil disimpan']);
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
            }
        }
        redirect('/');
    }

    //menampilkan halaman form edit user ajax
    public function edit_ajax(string $id)
    {
        $barang = BarangModel::find($id);
        $kategori = KategoriModel::all();

        return view('barang.edit_ajax', ['barang' => $barang,'kategori'=> $kategori]);
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $validator = Validator::make($request->all(), [
                'barang_kode' => 'required|string|max:10|unique:m_barang,barang_kode,'.$id.',barang_id',
                'barang_nama' => 'required|string|max:100',
                'harga_jual' => 'required|numeric',
                'harga_beli' => 'required|numeric',
                'kategori_id'=> 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors(),
                ]);
            }

            $barang = BarangModel::find($id);
            $barang->update($request->all());
            return response()->json(['status' => true, 'message' => 'Data barang berhasil diupdate']);
        }

        return redirect('/');
 }
    public function confirm_ajax(string $id)
    {
        $barang = BarangModel::find($id);
        $kategori = KategoriModel::all();
        return view('barang.confirm_ajax', ['barang' => $barang, 'kategori'=> $kategori]);
    }

    public function delete_ajax(Request $request, $id)
    {
        try {
            $barang = BarangModel::find($id);
            if ($barang) {
                $barang->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil dihapus'
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
    //     BarangModel::destroy($id);
    //     return redirect('/barang')->with('success', 'Barang berhasil dihapus');
    // }
}
