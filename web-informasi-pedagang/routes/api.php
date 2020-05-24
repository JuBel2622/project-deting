<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::apiResources(
    [
        'kurir' => 'API\RequestKurirControllers'
    ]
);

//kurir
Route::get('/kurirs/provinsi', 'RequestKurirControllers@test');
Route::get('/kurir/test/prov/{kode}', 'RequestKurirControllers@findKab');
Route::get('/kurir/test/kab/{kode}', 'RequestKurirControllers@findKec');
Route::get('/kurirs/find/kab/{kode}', 'RequestKurirControllers@findKec');
Route::post('/kurir/test/addImage/{kode}', 'RequestKurirControllers@addImage');
Route::get('/kurirs/ktp/', 'RequestKurirControllers@addKTP');
Route::post('upload/{kode}', 'RequestKurirControllers@addImage');
Route::post('upload/stnk/{kode}', 'RequestKurirControllers@addImageSTNK');
Route::post('upload/sim/{kode}', 'RequestKurirControllers@addImageSIM');
Route::post('upload/kurir/{kode}', 'RequestKurirControllers@addImageKurir');
Route::post('upload/all/', 'RequestKurirControllers@store');
Route::post('insert', 'RequestKurirControllers@masuk');
Route::get('check/', 'RequestKurirControllers@getKurir');
Route::get('kurirRequest/', 'RequestKurirControllers@findAllKurirRequest');
Route::get('kurirTerima/{id}', 'RequestKurirControllers@terima');
Route::get('kurirTolak/{id}', 'RequestKurirControllers@tolak');


//Pedagang
Route::get('pedagang/lihat/{kode}', 'RequestPedagangController@lihat');
Route::post('pedagang/upload/{kode}', 'RequestPedagangController@addImage');
Route::post('pedagang/insert', 'RequestPedagangController@stores');
Route::get('pedagangRequest/', 'RequestPedagangController@findAllPedagangRequest');
Route::get('pedagangTerima/{id}', 'RequestPedagangController@terima');
Route::get('pedagangTolak/{id}', 'RequestPedagangController@tolak');

//Produk
Route::post('produk/insert', 'RequestProdukController@stores');


Route::get('getprovinsi', function () {
    return \Illuminate\Support\Facades\DB::select('SELECT * FROM wilayah_2020 WHERE CHAR_LENGTH(kode)=2 ORDER BY nama');
});

//Route::get('/kurirs/provinsi','PedagangController@test');
//Route::get('/kurir/test/prov/{kode}','PedagangController@findKab');
//Route::get('/kurir/test/kab/{kode}','PedagangController@findKec');
//Route::get('/kurirs/find/kab/{kode}','PedagangController@findKec');
//Route::get('getprovinsi', function () {
//    return \Illuminate\Support\Facades\DB::select('SELECT * FROM wilayah_2020 WHERE CHAR_LENGTH(kode)=2 ORDER BY nama');
//});

Route::get('getproduct/{id}', function ($id) {
    return \Illuminate\Support\Facades\DB::select('SELECT * FROM produk WHERE id_pedagang=' . $id);
});
Route::get('getkab/{kode}', function ($kode) {
    return \Illuminate\Support\Facades\DB::select('SELECT * FROM wilayah_2020 WHERE kode like "' . $kode . '%" AND CHAR_LENGTH(kode)=5 ORDER BY nama');
});
Route::get('getkec/{kode}', function ($kode) {
    return \Illuminate\Support\Facades\DB::select('SELECT * FROM wilayah_2020 WHERE kode like "' . $kode . '%" AND CHAR_LENGTH(kode)=8 ORDER BY nama');
});
Route::get('getkel/{kode}', function ($kode) {
    return DB::select('SELECT * FROM wilayah_2020 WHERE kode like "' . $kode . '%" AND CHAR_LENGTH(kode)=13 ORDER BY nama');
});
Route::get('getkategoriproduk', function () {
    $as = DB::select('SELECT kategori from produk group by kategori');
    $cnt = count($as) / 3;
    $res = array();
    $offset = 0;
    for ($i = 0; $i < $cnt; $i++) {
        $temp = array();
        $temp['data'] = DB::select('SELECT kategori from produk group by kategori limit 3 offset ' . $offset);
        array_push($res, $temp);
        $offset += 3;
    }
    return $res;
});
Route::get('/getallprodbykat', function () {
    $kategori = $_GET['kat'];
    $produk = DB::select('SELECT pedagang.id, nama_produk, nama from produk inner join pedagang on pedagang.id = produk.id_pedagang where pedagang.status = 2 and produk.kategori = "' . $kategori . '"');
    return $produk;
});
Route::get('/searchpedagangbyid', function () {
    $id = $_GET['id'];
    $dataPedagang = DB::select('SELECT p.id, p.nama,p.alamat_rinci,p.no_wa,p.no_hp,p.foto_pedagang FROM pedagang p where p.id=' . $id);
    $dataPedagang = array_map(function ($value) {
        return (array)$value;
    }, $dataPedagang);
    $data = array();
    foreach ($dataPedagang as $ss) {
        $res = array();
        $res['pedagang'] = $ss;
        $dagangan = DB::select('SELECT * from produk where id_pedagang = ' . $ss['id']);
        $res['dagangan'] = $dagangan;
        array_push($data, $res);
    }
    return $data;
});
Route::get('/searchbykode', function () {
    $kode = $_GET['kode'];
    $produk = $_GET['produk'];
    $where = '';
    if ($produk !== '') {
        $where = ' AND lower(s.nama_produk) LIKE "%' . strtolower($produk) . '%"';
    }
    $data = array();
    $dataPedagang = DB::select('SELECT p.id, p.nama,p.alamat_rinci,p.no_wa,p.no_hp,p.foto_pedagang FROM pedagang p INNER JOIN produk s ON s.id_pedagang = p.id WHERE p.alamat like "' . $kode . '%"' . $where . ' AND p.status = 2 GROUP BY p.id, p.nama,p.alamat_rinci,p.no_wa,p.no_hp,p.foto_pedagang');
    $dataPedagang = array_map(function ($value) {
        return (array)$value;
    }, $dataPedagang);
    foreach ($dataPedagang as $ss) {
        $res = array();
        $res['pedagang'] = $ss;
        $dagangan = DB::select('SELECT * from produk where id_pedagang = ' . $ss['id']);
        $res['dagangan'] = $dagangan;
        array_push($data, $res);
    }
    return $data;
});
