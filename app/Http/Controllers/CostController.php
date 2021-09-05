<?php

namespace App\Http\Controllers;

use App\Models\Cost;
use App\Models\CostDetail;
use App\Models\HistoryCost;
use App\Models\HistoryCostDetail;

use Illuminate\Http\Request;
use DB;

class CostController extends Controller
{
    
    public function getAllCost(Request $request)
    {

        //Variable Pencarian
        $cari_nama_server = ucwords($request->input('nama_server'));
        $cari_tipe_server = $request->input('tipe_server');
        $cari_pic_team_server = $request->input('pic_team_server');
        $cari_lokasi_server = $request->input('lokasi_server');

        $tipe_sort = 'desc';
        $var_sort = 'updated_at';

        //Semua Cost
        $semuaCost = Cost::query();

        //Kondisi Search
        if($cari_nama_server != '') {
            $semuaCost = $semuaCost->where('nama_server','LIKE', '%'.$cari_nama_server.'%');
        }

        if($cari_tipe_server != '') {
            $semuaCost = $semuaCost->where('tipe_server','LIKE', '%'.$cari_tipe_server.'%');
        }

        if($cari_pic_team_server != '') {
            $semuaCost = $semuaCost->where('pic_team_server','LIKE', '%'.$cari_pic_team_server.'%');
        }

        if($cari_lokasi_server != '') {
            $semuaCost = $semuaCost->where('lokasi_server','LIKE', '%'.$cari_lokasi_server.'%');
        }
        
        if( $request->has('sortbydesc') || $request->has('sortby') ) {
            $tipe_sort = $request->input('sortbydesc');
            $var_sort = $request->input('sortby');
                                
            $semuaCost = $semuaCost->orderBy($var_sort, $tipe_sort);
        }

        $totalCost = $semuaCost->sum('total_cost');
        $totalPlatform = $semuaCost->count();

        // Eager Loading
        $semuaCost = $semuaCost->with('costDetails');
    
        //Tampilkan
        
        $set_pagination = $request->input('per_page');

        if ($set_pagination != '') {
            $semuaCost = $semuaCost
                        ->paginate($set_pagination);
        } else {
            $semuaCost = $semuaCost
                        ->paginate(10);
        }
    

        //Show the Data
        return response()->json([
            'message' => 'Data Berhasil di Ambil',
            'data'  => [
                'SemuaCost' => $semuaCost,
                'TotalCost' => $totalCost,
                'TotalPlatform' => $totalPlatform
            ]
        ], 200);

    }

    public function getTotalSummaryCost()
    {
        
        $totalCost = DB::table('costs')->sum('total_cost');
        $totalPlatform = Cost::count();

        $allCostData = Cost::get();

        $namaPlatform = [];
        $totalCostPlatform = [];

        foreach ($allCostData as $key) {
            $namaPlatform[] = $key->nama_server;
            $totalCostPlatform[] = $key->total_cost;
        }



        return response()->json([
            'message' => 'Data Berhasil di Ambil !',
            'data'    => [
                'TotalCost'        => $totalCost,
                'TotalPlatform'    => $totalPlatform,
                'NamaPlatform'     => $namaPlatform,
                'TotalCostPlatform'        => $totalCostPlatform
            ]
        ], 200);

    }

    public function createCost(Request $request)
    {

        // Pesan Jika Error
        $messages = [
            'nama_server.required'   => 'Masukkan Nama Server !',
            'nama_server.unique'     => 'Nama Server Sudah Ada !',
            'lokasi_server.required'   => 'Masukkan Lokasi Server !',
            'tipe_server.required'   => 'Masukkan Tipe Server !',
            'pic_team_server.required'   => 'Masukkan PIC Team !',

            'item_detail.required' => 'Masukkan Detail Item !',
        ];
        
        //Validasi Data
        $validasiData = $this->validate($request, [
            'nama_server'   => 'required|unique:costs',
            'lokasi_server'   => 'required',
            'tipe_server'   => 'required',
            'pic_team_server'   => 'required',

            'item_detail' => 'required',
        ], $messages);


        // Get Data Inputan - Cost
        $nama_server = $request->input('nama_server');
        $lokasi_server = $request->input('lokasi_server');
        $tipe_server = $request->input('tipe_server');
        $pic_team_server = $request->input('pic_team_server');

        // Get Data Inputan - Cost Detail
        $item_detail = $request->input('item_detail');        

        // Proses Simpan Data

        DB::beginTransaction();
       
        try {
            
            // Simpan Cost
            $dataCost = new Cost();
            $dataCost->nama_server = $nama_server;
            $dataCost->lokasi_server = $lokasi_server;
            $dataCost->tipe_server = $tipe_server;
            $dataCost->pic_team_server = $pic_team_server;
            $dataCost->edited_by_id = '1';

            $dataCost->save();

            // Simpan Cost Detail

            foreach ($item_detail as $key => $value) {

                // dd($key);
                $modelCD = new CostDetail();
                $modelCD->cost_id = $dataCost->id;
                $modelCD->nama_item = $value['nama_item'];
                $modelCD->harga_item = $value['harga_item'];
                $modelCD->save();

            }

            // Get Jumlah dari Harga Item per ID
            $total_cost_item = DB::table('cost_details')
                ->join('costs', 'cost_details.cost_id', '=', 'costs.id')
                ->where('costs.id', '=', $dataCost->id)
                ->sum('cost_details.harga_item');

            // Update Total Cost
            $dataCostUpdate = Cost::where('id', $dataCost->id)->first();

            if (!$dataCostUpdate) {
                return response()->json([
                    'message' => 'Data Tidak Ada !',
                ], 403);
            }

            $dataCostUpdate->total_cost = number_format($total_cost_item, 2, '.', '');

            $dataCostUpdate->save();

            // Jika Semua Normal, Commit ke DB
            DB::commit();

        } catch (\Exception $e) {
            // Jika ada yang Gagal, Rollback DB
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

       

        // Jika Berhasil Return Pesan
        return response()->json([
            'message' => 'data berhasil di Tambah !',
            'data'  => $dataCostUpdate
        ], 200);

    }

    public function updateCost(Request $request, $id)
    {

        // ------ Get Last Data ------
        $dataCostDetailCari = CostDetail::where('cost_id', $id)->get();
        $dataCost = Cost::where('id', $id)->first();

        // Cek Data Ada atau Tidak
        if(!$dataCostDetailCari) {
            return response()->json([
                'message' => 'Data Detail Tidak Ada !',
            ], 404);
        }

        if(!$dataCost) {
            return response()->json([
                'message' => 'Data Tidak Ada !',
            ], 404);
        }

        // ------ Copy to History ------

        // Proses Write ke History

        DB::beginTransaction();
       
        try {
            
            // Simpan Cost History
            $dataHistoryCost = new HistoryCost();
            $dataHistoryCost->nama_server = $dataCost->nama_server;
            $dataHistoryCost->lokasi_server = $dataCost->lokasi_server;
            $dataHistoryCost->tipe_server = $dataCost->tipe_server;
            $dataHistoryCost->pic_team_server = $dataCost->pic_team_server;
            $dataHistoryCost->edited_by_id = '1';
            $dataHistoryCost->cost_id = $id;

            $dataHistoryCost->save();


            foreach ($dataCostDetailCari as $key) {
                $modelHCD = new HistoryCostDetail();
                $modelHCD->history_cost_id = $dataHistoryCost->id;
                $modelHCD->nama_item = $key['nama_item'];
                $modelHCD->harga_item = $key['harga_item'];
                $modelHCD->save();
            }

            // Get Jumlah dari Harga Item per ID
            $total_cost_history_item = DB::table('history_cost_details')
                ->join('history_costs', 'history_cost_details.history_cost_id', '=', 'history_costs.id')
                ->where('history_costs.id', '=', $dataHistoryCost->id)
                ->sum('history_cost_details.harga_item');

            // Update Total Cost History
            $dataCostHistoryUpdate = HistoryCost::where('id', $dataHistoryCost->id)->first();

            if (!$dataCostHistoryUpdate) {
                return response()->json([
                    'message' => 'Data Tidak Ada !',
                ], 403);
            }

            $dataCostHistoryUpdate->total_cost = number_format($total_cost_history_item, 2, '.', '');

            $dataCostHistoryUpdate->save();

            // Jika Semua Normal, Commit ke DB
            DB::commit();

        } catch (\Exception $e) {
            // Jika ada yang Gagal, Rollback DB
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
        


        // ------ Hapus Dahulu ------ 

        // Mulai Proses Delete
        DB::beginTransaction();

        try {

            // Delete data Cost Detail
            $dataCostDetail = CostDetail::where('cost_id', $id);
            $dataCostDetail->delete();

            // Jika Semua Normal, Commit ke DB
            DB::commit();

        } catch (\Exception $e) {
            
            // Jika ada yang Gagal, Rollback DB
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);

        }

        // ------ Mulai Update ------ 

        // Pesan Jika Error
        $messages = [
            'nama_server.required'   => 'Masukkan Nama Server !',
            'nama_server.unique'     => 'Nama Server Sudah Ada !',
            'lokasi_server.required'   => 'Masukkan Lokasi Server !',
            'tipe_server.required'   => 'Masukkan Tipe Server !',
            'pic_team_server.required'   => 'Masukkan PIC Team !',

            'item_detail.required' => 'Masukkan Detail Item !',
        ];
        
        //Validasi Data
        $validasiData = $this->validate($request, [
            'nama_server'   => 'required|unique:costs,nama_server,' . $dataCost->id,
            'lokasi_server'   => 'required',
            'tipe_server'   => 'required',
            'pic_team_server'   => 'required',

            'item_detail' => 'required',
        ], $messages);


        // Get Data Inputan - Cost
        $nama_server = $request->input('nama_server');
        $lokasi_server = $request->input('lokasi_server');
        $tipe_server = $request->input('tipe_server');
        $pic_team_server = $request->input('pic_team_server');

        // Get Data Inputan - Cost Detail
        $item_detail = $request->input('item_detail');

        // Proses Simpan Data

        DB::beginTransaction();
       
        try {
            
            // Simpan Cost
            $dataCost->nama_server = $nama_server;
            $dataCost->lokasi_server = $lokasi_server;
            $dataCost->tipe_server = $tipe_server;
            $dataCost->pic_team_server = $pic_team_server;

            $dataCost->save();

            // Simpan Cost Detail

            foreach ($item_detail as $key) {
                $modelCD = new CostDetail();
                $modelCD->cost_id = $dataCost->id;
                $modelCD->nama_item = $key['nama_item'];
                $modelCD->harga_item = $key['harga_item'];
                $modelCD->save();
            }

            // Get Jumlah dari Harga Item per ID
            $total_cost_item = DB::table('cost_details')
                ->join('costs', 'cost_details.cost_id', '=', 'costs.id')
                ->where('costs.id', '=', $dataCost->id)
                ->sum('cost_details.harga_item');

            // Update Total Cost
            $dataCostUpdate = Cost::where('id', $dataCost->id)->first();

            if (!$dataCostUpdate) {
                return response()->json([
                    'message' => 'Data Tidak Ada !',
                ], 403);
            }

            $dataCostUpdate->total_cost = number_format($total_cost_item, 2, '.', '');

            $dataCostUpdate->save();

            // Jika Semua Normal, Commit ke DB
            DB::commit();

        } catch (\Exception $e) {
            // Jika ada yang Gagal, Rollback DB
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

       

        // Jika Berhasil Return Pesan
        return response()->json([
            'message' => 'data berhasil di Update !',
            'data'  => $dataCostUpdate
        ], 200);


    }

    public function deleteCost($id) 
    {
        $dataCost = Cost::where('id', $id)->first();
        $dataCostDetail = CostDetail::where('cost_id', $id);
        $dataHistoryCost = HistoryCost::where('cost_id', $id)->first();
        $dataHistoryCostDetail = HistoryCostDetail::where('history_cost_id', $id);

        // Cek Data Ada atau Tidak
        if(!$dataCost || !$dataCostDetail) {
            return response()->json([
                'message' => 'Data Tidak Ada !',
            ], 404);
        }

        // Mulai Proses Delete
        DB::beginTransaction();

        try {

            // Delete data Cost dan Cost Detail serta History
            $dataCost->delete();
            $dataCostDetail->delete();
            $dataHistoryCost->delete();
            $dataHistoryCostDetail->delete();

            // Jika Semua Normal, Commit ke DB
            DB::commit();

        } catch (\Exception $e) {
            
            // Jika ada yang Gagal, Rollback DB
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);

        }

        // Jika Berhasil Return Pesan
        return response()->json([
            'message' => 'data berhasil di Hapus !',
        ], 200);

    }

    public function getHistoryCost($id)
    {

        // Data History Cost
        $dataHistoryCost = HistoryCost::where('cost_id', $id)
                            // ->orderBy('updated_at', 'desc')
                            ->with('historyCostDetails')
                            ->get();

        // Data Cost
        $dataCost = Cost::where('id', $id)->with('costDetails')->first();

        $tanggalUpdate = [];
        $totalCost = [];

        foreach ($dataHistoryCost as $key) {
            // $tanggalUpdate[] = date('d-M-Y', strtotime($key->updated_at));
            $tanggalUpdate[] = $key->updated_at;
            $totalCost[] = $key->total_cost;
        }

        return response()->json([
            'message' => 'Data Berhasil di Ambil !',
            'data'    => [
                'HistoryCost'        => $dataHistoryCost,
                'TotalCost'          => $totalCost,
                'TanggalUpdate'      => $tanggalUpdate,
                'DataCost'           => $dataCost
            ]
        ], 200);


        
    }
    
}
