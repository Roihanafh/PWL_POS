<?php

namespace App\Http\Controllers;

use App\Models\LevelModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF;

class LevelController extends Controller
{
     // Menampilkan halaman daftar level
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Level',
            'list' => ['Home', 'Level']
        ];

        $page = (object) [
            'title' => 'Daftar Level yang tersedia dalam sistem'
        ];

        $activeMenu = 'level';
        return view('level.index', compact('breadcrumb', 'page', 'activeMenu'));
    }

    // Mengambil data level dalam bentuk JSON untuk DataTables
    public function list()
    {
        $levels = LevelModel::select('level_id', 'level_nama', 'level_kode')->get();
        return DataTables::of($levels)
            ->addIndexColumn()
            ->addColumn('aksi', function ($level) {
                // $btn = '<a href="'.url('/level/' . $level->level_id). '" class="btn btn-info btn-sm">Detail</a> ';
                // $btn .= '<a href="'.url('/level/' . $level->level_id . '/edit'). '" class="btn btn-warning btn-sm">Edit</a> ';
                // $btn .= '<form class="d-inline-block" method="POST" action="'.url('/level/'.$level->level_id).'">'. csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin menghapus data ini?\');">Hapus</button></form>';
                $btn = '<button onclick="modalAction(\''.url('/level/' . $level->level_id . '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/level/' . $level->level_id . '/delete_ajax').'\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    // Menampilkan form tambah level
    public function create()
    {
        $breadcrumb = (object) [
            'title' => 'Tambah Level',
            'list' => ['Home', 'Level', 'Tambah']
        ];

        $page = (object) [
            'title' => 'Tambah level baru'
        ];

        $activeMenu = 'level';
        return view('level.create', compact('breadcrumb', 'page', 'activeMenu'));
    }

    // Menyimpan level baru
    public function store(Request $request)
    {
        $request->validate([
            'level_nama' => 'required|string|unique:m_level,level_nama'
        ]);

        LevelModel::create([
            'level_kode' => $request->level_kode, // Pastikan input ini ada di form
            'level_nama' => $request->level_nama,
        ]);
        
        return redirect('/level')->with('success', 'Data level berhasil disimpan');
    }

    // Menampilkan detail level
    public function show($id)
    {
        $level = LevelModel::findOrFail($id);

        $breadcrumb = (object) [
            'title' => 'Detail Level',
            'list' => ['Home', 'Level', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail level'
        ];

        $activeMenu = 'level';
        return view('level.show', compact('breadcrumb', 'page', 'level', 'activeMenu'));
    }

    // Menampilkan form edit level
    public function edit($id)
    {
        $level = LevelModel::findOrFail($id);

        $breadcrumb = (object) [
            'title' => 'Edit Level',
            'list' => ['Home', 'Level', 'Edit']
        ];

        $page = (object) [
            'title' => 'Edit level'
        ];

        $activeMenu = 'level';
        return view('level.edit', compact('breadcrumb', 'page', 'level', 'activeMenu'));
    }

    // Menyimpan perubahan data level
    public function update(Request $request, $id)
    {
        $request->validate([
            'level_nama' => 'required|string|unique:m_level,level_nama,' . $id . ',level_id'
        ]);

        LevelModel::where('level_id', $id)->update(['level_nama' => $request->level_nama]);
        return redirect('/level')->with('success', 'Data level berhasil diperbarui');
    }

    // Menghapus data level
    public function destroy($id)
    {
        $level = LevelModel::find($id);
        if (!$level) {
            return redirect('/level')->with('error', 'Data level tidak ditemukan');
        }

        try {
            LevelModel::destroy($id);
            return redirect('/level')->with('success', 'Data level berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect('/level')->with('error', 'Data level gagal dihapus karena masih terkait dengan data lain');
        }
    }

    public function create_ajax()
    {
        return view('level.create_ajax');
    }

    public function store_ajax(Request $request){
        //cek apakah request berupa ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules=[
                'level_nama' => 'required|string|unique:m_level,level_nama',
                'level_kode'=> 'required|string|min:3|unique:m_level,level_kode'
                ];
            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails() ) {
                return response()->json([
                'status' => false,//responser status, false error, true berhasil
                'message'=> 'Validasi gagal',
                'msgField'=>$validator->errors(), //pesan error validasi
                
                ]);
            }
            LevelModel::create($request->all());
            return response()->json([
                'status'=> true,
                'message'=> 'Data berhasil disimpan'
            ]);
        }
        redirect('/');
    }

    //menampilkan halaman form edit user ajax
    public function edit_ajax(string $id)
    {
        $level = LevelModel::find($id);

        return view('level.edit_ajax', ['level' => $level]);
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules=[
                'level_nama' => 'required|string|unique:m_level,level_nama,'.$id.',level_id',
                'level_kode'=> 'required|string|min:3'
                ];
            $validator = Validator::make($request->all(), $rules);
            if( $validator->fails() ) {
                return response()->json([
                'status'=> false,
                'message'=> 'Validasi gagal',
                'msgField'=>$validator->errors(),
                ]);
            }
            $check = LevelModel::find($id);
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
        $level = LevelModel::find($id);
        return view('level.confirm_ajax', ['level' => $level]);
    }

    public function delete_ajax(Request $request, $id)
    {
        try {
            $level = LevelModel::find($id);
            if ($level) {
                $level->delete();
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
        return view('level.import');
    }
    public function import_ajax(Request $request)
    {
        if($request->ajax() || $request->wantsJson()){
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_level' => ['required', 'mimes:xlsx', 'max:1024']
            ];
            $validator = Validator::make($request->all(), $rules);
            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }
            $file = $request->file('file_level'); // ambil file dari request
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
                            'level_kode' => $value['A'],
                            'level_nama' => $value['B'],
                            'created_at' => now(),
                        ];
                    }
                }
                if(count($insert) > 0){
                    // insert data ke database, jika data sudah ada, maka diabaikan
                    LevelModel::insertOrIgnore($insert);
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
        return redirect('/level');
    }
    public function export_excel()
    {
        // ambil data level yang akan di export
        $level = LevelModel::select('level_id','level_kode','level_nama')
                    ->orderBy('level_id')
                    ->get();
        // load library excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();        // ambil sheet yang aktif
    
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'ID level');
        $sheet->setCellValue('C1', 'Kode level');
        $sheet->setCellValue('D1', 'Nama level');

        $sheet->getStyle('A1:D1')->getFont()->setBold(true);    // bold header
        
        $no = 1;          // nomor data dimulai dari 1
        $baris = 2;       // baris data dimulai dari baris ke 2
        foreach ($level as $key => $value) {
            $sheet->setCellValue('A'.$baris, $no);
            $sheet->setCellValue('B'.$baris, $value->level_id);
            $sheet->setCellValue('C'.$baris, $value->level_kode);
            $sheet->setCellValue('D'.$baris, $value->level_nama);
            $baris++;
            $no++;
        }
    
        foreach (range('A','D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        $sheet->setTitle('Data level'); // set title sheet
        $writer=IOFactory::createWriter ($spreadsheet, 'Xlsx');
        $filename = 'Data level ' .date('Y-m-d H:i:s').'.xlsx';
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
        $level = LevelModel::select('level_id','level_kode','level_nama')
                    ->orderBy('level_id')
                    ->get();
        //use Barryvdh\DomPDF\Facade\Pdf;
        $pdf = FacadePdf::loadView('level.export_pdf', ['level' => $level]);
        $pdf->setPaper('A4', 'portrait');//set ukuran kertas dan orientasi
        $pdf->setOption('isRemoteEnabled', true);//set true jika ada gambar dari url
        $pdf->render();

        return $pdf->stream('Data level ' .date('Y-m-d H:i:s').'.pdf');
    }
    // public function index()
    // {
        
        // DB::insert('insert into m_level(level_kode, level_nama, created_at) values(?,?,?)', ['CUS', 'Pelanggan', now()]);
        // return 'insert data baru berhasil';

        // $row = DB::update('update m_level set level_nama = ? where level_kode = ?', ['Customer','CUS']);
        // return 'update data berhasil. Jumlah data yang diupdate: ' . $row . ' baris';

        // $row =DB::delete('delete from m_level where level_kode = ?', ['CUS']);
        // return 'delete data berhasil. Jumlah data yang dihapus: ' . $row . ' baris';

        // $data = DB::select('select * from m_level');
        // return view('level', ['data' => $data]);
    // }
}
