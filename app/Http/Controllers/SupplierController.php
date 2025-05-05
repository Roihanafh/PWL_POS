<?php

namespace App\Http\Controllers;

use App\Models\SupplierModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF;

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
                // return '<a href="'.url('/supplier/'.$supplier->supplier_id.'/edit').'" class="btn btn-warning btn-sm">Edit</a> '
                //     .'<form class="d-inline-block" method="POST" action="'.url('/supplier/'.$supplier->supplier_id).'">'
                //     .csrf_field().method_field('DELETE')
                //     .'<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Yakin hapus data?\');">Hapus</button></form>';
                $btn = '<button onclick="modalAction(\''.url('/supplier/' . $supplier->supplier_id . '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/supplier/' . $supplier->supplier_id . '/delete_ajax').'\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
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
        $supplier = SupplierModel::find($id);
        if (!$supplier) {
            return redirect('/supplier')->with('error', 'Data supplier tidak ditemukan');
        }

        try {
            SupplierModel::destroy($id);
            return redirect('/supplier')->with('success', 'Data supplier berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/supplier')->with('error', 'Data supplier gagal dihapus karena masih terkait dengan data lain');
        }
    }
    public function create_ajax()
{
    return view('supplier.create_ajax');
}

public function store_ajax(Request $request){
    if ($request->ajax() || $request->wantsJson()) {
        $rules=[
            'supplier_nama' => 'required|string|unique:m_supplier,supplier_nama',
            'supplier_kode'=> 'required|string|min:3|unique:m_supplier,supplier_kode',
            'supplier_alamat' => 'required|string'
        ];
        $validator = Validator::make($request->all(), $rules);
        if( $validator->fails() ) {
            return response()->json([
                'status' => false,
                'message'=> 'Validasi gagal',
                'msgField'=>$validator->errors(),
            ]);
        }
        SupplierModel::create($request->all());
        return response()->json([
            'status'=> true,
            'message'=> 'Data berhasil disimpan'
        ]);
    }
    redirect('/');
}

public function edit_ajax(string $id)
{
    $supplier = SupplierModel::find($id);
    return view('supplier.edit_ajax', ['supplier' => $supplier]);
}

public function update_ajax(Request $request, $id)
{
    if ($request->ajax() || $request->wantsJson()) {
        $rules=[
            'supplier_nama' => 'required|string|unique:m_supplier,supplier_nama,'.$id.',supplier_id',
            'supplier_kode'=> 'required|string|min:3',
            'supplier_alamat' => 'required|string'
        ];
        $validator = Validator::make($request->all(), $rules);
        if( $validator->fails() ) {
            return response()->json([
                'status'=> false,
                'message'=> 'Validasi gagal',
                'msgField'=>$validator->errors(),
            ]);
        }
        $check = SupplierModel::find($id);
        if ($check) {
            $check->update($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } else{
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }
    return redirect('/');
}

public function confirm_ajax(string $id)
{
    $supplier = SupplierModel::find($id);
    return view('supplier.confirm_ajax', ['supplier' => $supplier]);
}

public function delete_ajax(Request $request, $id)
{
    try {
        $supplier = SupplierModel::find($id);
        if ($supplier) {
            $supplier->delete();
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
public function import()
    {
        return view('supplier.import');
    }
    public function import_ajax(Request $request)
    {
        if($request->ajax() || $request->wantsJson()){
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_supplier' => ['required', 'mimes:xlsx', 'max:1024']
            ];
            $validator = Validator::make($request->all(), $rules);
            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }
            $file = $request->file('file_supplier'); // ambil file dari request
            $reader = IOFactory::createReader('Xlsx'); // load reader file excel
            $reader->setReadDataOnly(true); // hanya membaca data
            $spreadsheet = $reader->load($file->getRealPath()); // load file excel
            $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif
            $data = $sheet->toArray(null, false, true, true); // ambil data excel
            $insert = [];
            if(count($data) > 1){ // jika data lebih dari 1 baris
                foreach ($data as $baris => $value) {
                    if($baris > 1){ // baris ke 1 adalah header, maka lewati
                        $insert[] = [
                            'supplier_kode' => $value['A'],
                            'supplier_nama' => $value['B'],
                            'supplier_alamat' => $value['C'],
                            'created_at' => now(),
                        ];
                    }
                }
                if(count($insert) > 0){
                    // insert data ke database, jika data sudah ada, maka diabaikan
                    SupplierModel::insertOrIgnore($insert);
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
        return redirect('/supplier');
    }
    public function export_excel()
    {
        // ambil data supplier yang akan di export
        $supplier = SupplierModel::select('supplier_id','supplier_kode','supplier_nama','supplier_alamat')
                    ->orderBy('supplier_id')
                    ->get();
        // load library excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();        // ambil sheet yang aktif
    
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode supplier');
        $sheet->setCellValue('C1', 'Nama supplier');
        $sheet->setCellValue('D1', 'Alamat supplier');

        $sheet->getStyle('A1:D1')->getFont()->setBold(true);    // bold header
        
        $no = 1;          // nomor data dimulai dari 1
        $baris = 2;       // baris data dimulai dari baris ke 2
        foreach ($supplier as $key => $value) {
            $sheet->setCellValue('A'.$baris, $no);
            $sheet->setCellValue('B'.$baris, $value->supplier_kode);
            $sheet->setCellValue('C'.$baris, $value->supplier_nama);  
            $sheet->setCellValue('D'.$baris, $value->supplier_alamat);
            $baris++;
            $no++;
        }
    
        foreach (range('A','D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        $sheet->setTitle('Data Supplier'); // set title sheet
        $writer=IOFactory::createWriter ($spreadsheet, 'Xlsx');
        $filename = 'Data supplier ' .date('Y-m-d H:i:s').'.xlsx';
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
        $supplier = SupplierModel::select('supplier_id','supplier_kode','supplier_nama','supplier_alamat')
                    ->orderBy('supplier_id')
                    ->get();
        //use Barryvdh\DomPDF\Facade\Pdf;
        $pdf = FacadePdf::loadView('supplier.export_pdf', ['supplier' => $supplier]);
        $pdf->setPaper('A4', 'portrait');//set ukuran kertas dan orientasi
        $pdf->setOption('isRemoteEnabled', true);//set true jika ada gambar dari url
        $pdf->render();

        return $pdf->stream('Data Supplier ' .date('Y-m-d H:i:s').'.pdf');
    }
}
