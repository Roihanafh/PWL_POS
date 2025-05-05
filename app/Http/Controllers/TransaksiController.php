<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\PenjualanModel;
use App\Models\PenjualanDetailModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class TransaksiController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Transaksi',
            'list' => ['Home', 'Transaksi']
        ];

        $page = (object) [
            'title' => 'Data Transaksi'
        ];

        $pembeli = PenjualanModel::select('penjualan_id', 'pembeli')
            ->get();
        $user = UserModel::select('user_id', 'username')
            ->get();

        $activeMenu = 'transaksi';

        return view('transaksi.index', compact('breadcrumb', 'page', 'pembeli', 'user', 'activeMenu'));
    }

    public function list(Request $request)
    {
        $penjualan = PenjualanModel::select('penjualan_id', 'user_id','penjualan_kode', 'pembeli', 'penjualan_tanggal')
            ->orderBy('penjualan_id')
            ->with('user')
            ->get();
        
        if ($request->penjualan_id) {
            $penjualan = $penjualan->where('penjualan_id', $request->penjualan_id);
        }
        if ($request->user_id) {
            $penjualan = $penjualan->where('user_id', $request->user_id);
        }

        return DataTables::of($penjualan)
            ->addIndexColumn()
            ->addColumn('aksi', function ($penjualan) {
                $btn='<button onclick="modalAction(\'' . url("transaksi/show_ajax/" . $penjualan->penjualan_id) . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\''.url('transaksi/' . $penjualan->penjualan_id . '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\''.url('transaksi/' . $penjualan->penjualan_id . '/delete_ajax').'\')" class="btn btn-danger btn-sm">Hapus</button> ';
                    return $btn;   
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show_ajax($id)
    {
        // Ambil data penjualan utama beserta user/kasir
        $penjualan = PenjualanModel::with('user') // relasi ke m_user
            ->where('penjualan_id', $id)
            ->first();

        // Jika data penjualan tidak ditemukan
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Penjualan tidak ditemukan.',
            ], 404);
        }

        // Ambil detail penjualan + data barang
        $detail_penjualan = PenjualanDetailModel::with('barang') // relasi ke m_barang
            ->where('penjualan_id', $id)
            ->get();

        return view('transaksi.show_ajax', compact('penjualan', 'detail_penjualan'));
    }
    public function create_ajax()
    {
        // Ambil data penjualan utama beserta user/kasir
        $penjualan = PenjualanModel::with('user') // relasi ke m_user
            ->first();

        // Jika data penjualan tidak ditemukan
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Penjualan tidak ditemukan.',
            ], 404);
        }

        // Ambil detail penjualan + data barang
        $penjualanDetail = PenjualanDetailModel::with('barang') // relasi ke m_barang
            ->get();
        $barang = BarangModel::all();
        
        return view('transaksi.create_ajax', compact('penjualan', 'penjualanDetail','barang'));
    }

    public function store_ajax(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validasi input
            $request->validate([
                'pembeli' => 'required|string',
                'barang_id' => 'required|array',
                'barang_id.*' => 'exists:m_barang,barang_id',
                'harga' => 'required|array',
                'jumlah' => 'required|array',
            ]);

            // Simpan ke tabel penjualan
            $penjualan = PenjualanModel::create([
                'user_id' => $request->user_id,
                'pembeli' => $request->pembeli,
                'penjualan_tanggal' => $request->penjualan_tanggal,
                'penjualan_kode' => 'PNJ-' . strtoupper(Str::random(6)),
            ]);

            // Simpan ke tabel detail
            foreach ($request->barang_id as $i => $barang_id) {
                PenjualanDetailModel::create([
                    'penjualan_id' => $penjualan->penjualan_id,
                    'barang_id' => $barang_id,
                    'harga' => $request->harga[$i],
                    'jumlah' => $request->jumlah[$i],
                ]);
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Transaksi berhasil disimpan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }



    public function edit_ajax(string $id)
    {
        // Ambil data penjualan berdasarkan ID, beserta user/kasir-nya
        $penjualan = PenjualanModel::with('user') // relasi ke m_user
            ->where('penjualan_id', $id)
            ->first();

        // Jika tidak ditemukan
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Penjualan tidak ditemukan.',
            ], 404);
        }

        // Ambil detail penjualan berdasarkan penjualan_id + relasi barang
        $penjualanDetail = PenjualanDetailModel::with('barang') // relasi ke m_barang
            ->where('penjualan_id', $id)
            ->get();

        // Ambil semua data barang untuk dropdown
        $barang = BarangModel::all();

        return view('transaksi.edit_ajax', compact('penjualan', 'penjualanDetail', 'barang'));
    }


    public function update_ajax(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Validasi
            $request->validate([
                'pembeli' => 'required|string',
                'barang_id' => 'required|array',
                'barang_id.*' => 'exists:m_barang,barang_id',
                'harga' => 'required|array',
                'jumlah' => 'required|array',
            ]);

            // Update data penjualan
            $penjualan = PenjualanModel::findOrFail($id);
            $penjualan->user_id = $request->user_id;
            $penjualan->pembeli = $request->pembeli;
            $penjualan->save();

            // Hapus detail lama
            PenjualanDetailModel::where('penjualan_id', $id)->delete();

            // Tambah detail baru
            foreach ($request->barang_id as $i => $barang_id) {
                PenjualanDetailModel::create([
                    'penjualan_id' => $penjualan->penjualan_id,
                    'barang_id' => $barang_id,
                    'harga' => $request->harga[$i],
                    'jumlah' => $request->jumlah[$i],
                ]);
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Data berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function confirm_ajax(string $id)
    {
        $transaksi = PenjualanModel::find($id);

        return view('transaksi.confirm_ajax', compact('transaksi'));
    }


    public function delete_ajax($id)
    {
        try {
            // Hapus transaksi dan detailnya
            DB::beginTransaction();

            // Cari semua data penjualanDetail yang terkait dengan transaksi tertentu
            $penjualanDetails = PenjualanDetailModel::where('penjualan_id', $id)->get();

            // Hapus data penjualanDetail yang ditemukan
            $penjualanDetails->each(function($detail) {
                $detail->delete();
            });

            // Setelah penjualanDetail dihapus, sekarang kamu bisa menghapus PenjualanModel jika diperlukan
            $transaksi = PenjualanModel::findOrFail($id);
            $transaksi->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Data transaksi berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()
            ]);
        }
    }



    public function import()
    {
        return view('transaksi.import');
    }
    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'file_transaksi' => ['required', 'mimes:xlsx', 'max:1024']
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            $file = $request->file('file_transaksi');
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, false, true, true);

            $penjualanMap = []; // Untuk menyimpan id penjualan berdasarkan kode
            $insertDetail = [];

            if (count($data) > 1) {
                DB::beginTransaction();
                try {
                    foreach ($data as $baris => $row) {
                        if ($baris > 1) {
                            $kode = $row['B'];
                            // Cek apakah kode penjualan sudah dibuat
                            if (!isset($penjualanMap[$kode])) {
                                $penjualan = PenjualanModel::create([
                                    'penjualan_kode' => $kode,
                                    'penjualan_tanggal' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['A'])->format('Y-m-d'),
                                    'pembeli' => $row['C'],
                                    'user_id' => $row['D'],
                                ]);
                                $penjualanMap[$kode] = $penjualan->penjualan_id;
                            }

                            $insertDetail[] = [
                                'penjualan_id' => $penjualanMap[$kode],
                                'barang_id' => $row['E'],
                                'jumlah' => $row['F'],
                                'harga' => 0 // Optional: bisa diganti jika ada kolom harga
                            ];
                        }
                    }

                    PenjualanDetailModel::insert($insertDetail);

                    DB::commit();
                    return response()->json([
                        'status' => true,
                        'message' => 'Data transaksi berhasil diimport'
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada data yang diimport'
                ]);
            }
        }

        return redirect('/transaksi');
    }

    public function export_excel()
    {
        $penjualan = PenjualanModel::with('penjualanDetail.barang')->get();
        $users = UserModel::pluck('username', 'user_id'); // key: user_id, value: username

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Tanggal');
        $sheet->setCellValue('C1', 'Kode Penjualan');
        $sheet->setCellValue('D1', 'Nama Pembeli');
        $sheet->setCellValue('E1', 'Nama Kasir');
        $sheet->setCellValue('F1', 'Nama Barang');
        $sheet->setCellValue('G1', 'Qty');
        $sheet->setCellValue('H1', 'Harga');
        $sheet->setCellValue('I1', 'Total');

        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $baris = 2;
        $no = 1;

        foreach ($penjualan as $p) {
            $username = $users[$p->user_id] ?? 'Tidak Diketahui';

            foreach ($p->penjualanDetail as $detail) {
                $sheet->setCellValue('A' . $baris, $no);
                $sheet->setCellValue('B' . $baris, $p->penjualan_tanggal);
                $sheet->setCellValue('C' . $baris, $p->penjualan_kode);
                $sheet->setCellValue('D' . $baris, $p->pembeli);
                $sheet->setCellValue('E' . $baris, $username);
                $sheet->setCellValue('F' . $baris, $detail->barang->barang_nama ?? '-');
                $sheet->setCellValue('G' . $baris, $detail->jumlah);
                $sheet->setCellValue('H' . $baris, $detail->harga);
                $sheet->setCellValue('I' . $baris, $detail->jumlah * $detail->harga);
                $baris++;
                $no++;
            }
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setTitle('Data Transaksi');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Transaksi ' . date('Y-m-d H:i:s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }


    public function export_pdf()
    {
        $penjualan = PenjualanModel::with('penjualanDetail.barang')->get();
        $users = UserModel::pluck('username', 'user_id');

        return FacadePdf::loadView('transaksi.export_pdf', [
            'penjualan' => $penjualan,
            'users' => $users
        ])
        ->setPaper('A4', 'portrait')
        ->setOption('isRemoteEnabled', true)
        ->stream('Data Transaksi ' . date('Y-m-d H:i:s') . '.pdf');
    }



}
