<?php 
require_once 'app/models/Transaksi.php';
require_once 'config/database.php';
require_once 'app/models/Produk.php';

class TransaksiController
{
    public function daftarTransaksi()
    {
        $transaksi = new Transaksi();
        $data = $transaksi->tampilkanSemuaTransaksi();
        require 'app/views/transaksi_view.php';
    }

    public function tambah()
    {
        $database = new Database();
        $conn = $database->getConnection();

        $barang = [];
        $result = $conn->query("SELECT * FROM barang");
        if ($result && $result->num_rows > 0) {
            $barang = $result->fetch_all(MYSQLI_ASSOC);
        }

        $pelanggan = [];
        $result2 = $conn->query("SELECT * FROM pelanggan");
        if ($result2 && $result2->num_rows > 0) {
            $pelanggan = $result2->fetch_all(MYSQLI_ASSOC);
        }

        require 'app/Views/transaksi_form.php';
    }
    public function simpan()
    {
        $transaksi = new Transaksi();
        $produk = new Produk(); 

        $id_pelanggan = $_POST['id_pelanggan'];
        $barang_dibeli = $_POST['barang'];

        $database = new Database();
        $conn = $database->getConnection();

        $dataBarang = [];
        $result = $conn->query("SELECT kode_barang, harga FROM barang");
        while ($row = $result->fetch_assoc()) {
            $dataBarang[$row['kode_barang']] = $row['harga'];
        }

        foreach ($barang_dibeli as $kode_barang => $jumlah) {
            if ($jumlah > 0 && isset($dataBarang[$kode_barang])) {

                $harga = $dataBarang[$kode_barang];
                $total_harga = $harga * $jumlah;
                $transaksi->tambahTransaksi([
                    'kode_barang'  => $kode_barang,
                    'id_pelanggan' => $id_pelanggan,
                    'jumlah'       => $jumlah,
                    'total_harga'  => $total_harga
                ]);
                $produk->kurangiStok($kode_barang, $jumlah);
            }
        }

        header('Location: index.php?action=transaksi');
        exit;
    }

    public function edit($id)
    {
        $transaksi = new Transaksi();
        $data = $transaksi->tampilkanTransaksiById($id);
        require 'app/Views/transaksi_form.php';
    }

    public function update($id)
    {
        $transaksi = new Transaksi();
        $transaksi->updateTransaksi($id, $_POST);
        header('Location: index.php?action=transaksi');
    }

    public function hapus($id)
    {
        $transaksi = new Transaksi();
        $transaksi->hapusTransaksi($id);
        header('Location: index.php?action=transaksi');
    }

    public function detail()
    {
        $model = new Transaksi();
        $id = $_GET['id'];
        $data = $model->getById($id);

        require 'app/views/detail_transaksi.php';
    }
}
?>
