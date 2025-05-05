<?php

namespace App\Http\Controllers;

use App\Models\StokModel;
use App\Models\SupplierModel;
use App\Models\BarangModel;
use App\Models\KategoriModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
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

        $user = UserModel::select('user_id', 'username')->get();
        $supplier = SupplierModel::select('supplier_id', 'supplier_nama')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama')->get();

        $activeMenu = 'stok';
        
        return view('stok.index', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'user' => $user,
            'supplier' => $supplier,
            'barang' => $barang,
            'activeMenu' => $activeMenu
        ]);
    }

    public function list(Request $request)
    {
        $stok = StokModel::select('stok_id','supplier_id','barang_id','user_id','stok_tanggal','stok_jumlah')
            ->with('supplier')
            ->with('barang')
            ->with('user');
        
        if ($request->supplier_id) {
            $stok = $stok->where('supplier_id', $request->supplier_id);
        }
        if ($request->barang_id) {
            $stok = $stok->where('barang_id', $request->barang_id);
        }
        if ($request->user_id) {
            $stok = $stok->where('user_id', $request->user_id);
        }

        return DataTables::of($stok)
            ->addIndexColumn()
            ->addColumn('aksi', function ($stok) {
                $btn = '<button onclick="modalAction(\''.url('/stok/' . $stok->stok_id . '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> ';
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

            // Validasi awal
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'msgField' => $validator->errors(),
                ]);
            }

            // Validasi kombinasi unik barang_id dan supplier_id
            $exists = StokModel::where('supplier_id', $request->supplier_id)
                ->where('barang_id', $request->barang_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kombinasi barang dan supplier sudah ada',
                    'msgField' => [
                        'barang_id' => ['Barang dan supplier sudah pernah dicatat'],
                        'supplier_id' => ['Barang dan supplier sudah pernah dicatat'],
                    ]
                ]);
            }

            // Simpan data
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
    }

    public function import()
    {
        return view('stok.import');
    }
    public function import_ajax(Request $request)
    {
        if($request->ajax() || $request->wantsJson()){
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_stok' => ['required', 'mimes:xlsx', 'max:1024']
            ];
            $validator = Validator::make($request->all(), $rules);
            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }
            $file = $request->file('file_stok'); // ambil file dari request
            $reader = IOFactory::createReader('Xlsx'); // load reader file excel
            $reader->setReadDataOnly(true); // hanya membaca data
            $spreadsheet = $reader->load($file->getRealPath()); // load file excel
            $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif
            $data = $sheet->toArray(null, false, true, true); // ambil data excel
            $insert = [];
            if(count($data) > 1){ // jika data lebih dari 1 baris
                foreach ($data as $baris => $value) {
                    if($baris > 1){ // baris ke 1 adalah header, maka lewati
                        $tanggal = Date::excelToDateTimeObject($value['D'])->format('Y-m-d H:i:s');
                        $insert[] = [
                            'supplier_id' => $value['A'],
                            'barang_id' => $value['B'],
                            'user_id' => $value['C'],
                            'stok_tanggal' => $tanggal,
                            'stok_jumlah' => $value['E'],
                            'created_at' => now(),
                        ];
                    }
                }
                if(count($insert) > 0){
                    // insert data ke database, jika data sudah ada, maka diabaikan
                    StokModel::insertOrIgnore($insert);
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'Data berhasil diimport'
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Tidak ada data yang diimport'
                    ]);
                }
            }
    }
    public function export_excel()
    {
        // ambil data stok yang akan di export
        $stok = StokModel::select('supplier_id','barang_id','user_id','stok_tanggal','stok_jumlah')
                    ->orderBy('stok_id')
                    ->with('supplier')
                    ->with('barang')
                    ->with('user')
                    ->get();
        // load library excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();        // ambil sheet yang aktif
    
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Supplier');
        $sheet->setCellValue('C1', 'Nama Barang');
        $sheet->setCellValue('D1', 'Nama Penanggung Jawab');
        $sheet->setCellValue('E1', 'Stok Tanggal');
        $sheet->setCellValue('F1', 'Jumlah Stok');
    
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);    // bold header
        
        $no = 1;          // nomor data dimulai dari 1
        $baris = 2;       // baris data dimulai dari baris ke 2
        foreach ($stok as $key => $value) {
            $sheet->setCellValue('A'.$baris, $no);
            $sheet->setCellValue('B'.$baris, $value->supplier->supplier_nama);
            $sheet->setCellValue('C'.$baris, $value->barang->barang_nama);
            $sheet->setCellValue('D'.$baris, $value->user->username);
            $sheet->setCellValue('E'.$baris, $value->stok_tanggal);
            $sheet->setCellValue('F'.$baris, $value->stok_jumlah);
            $baris++;
            $no++;
        }
    
        foreach (range('A','F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        $sheet->setTitle('Data Stok'); // set title sheet
        $writer=IOFactory::createWriter ($spreadsheet, 'Xlsx');
        $filename = 'Data Stok ' .date('Y-m-d H:i:s').'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename. '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified:' . gmdate ('D, d MY H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $writer->save('php://output');
        exit;
    } //end function export_excel
    public function export_pdf(){
        $stok = StokModel::select('supplier_id','barang_id','user_id','stok_tanggal','stok_jumlah')
                    ->orderBy('stok_id')
                    ->with('supplier')
                    ->with('barang')
                    ->with('user')
                    ->get();
        //use Barryvdh\DomPDF\Facade\Pdf;
        $pdf = FacadePdf::loadView('stok.export_pdf', ['stok' => $stok]);
        $pdf->setPaper('A4', 'portrait');//set ukuran kertas dan orientasi
        $pdf->setOption('isRemoteEnabled', true);//set true jika ada gambar dari url
        $pdf->render();

        return $pdf->stream('Data Stok ' .date('Y-m-d H:i:s').'.pdf');
    }
    // public function destroy($id)
    // {
    //     StokModel::destroy($id);
    //     return redirect('/stok')->with('success', 'Stok berhasil dihapus');
    // }
}
