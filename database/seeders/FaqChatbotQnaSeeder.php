<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FaqChatbotQna;

class FaqChatbotQnaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'question' => 'Gadai Hp',
                'answer' => 'Silahkan informasikan secara detil :

Merk Hp:
Tipe & RAM Hp:
Kelengkapan (Fullset Like New/Fullset/...)',
            ],
            [
                'question' => 'Gadai Laptop',
                'answer' => 'Silahkan informasikan secara detil :

Merk Laptop:
Prosesor Intel Core/ AMD apa:
Kelengkapan Apa Saja:',
            ],
            [
                'question' => 'Gadai Kamera',
                'answer' => 'Silahkan informasikan secara detil :

Kamera Yang Ingin Digadai Apa:
Kelengkapan Apa Saja:',
            ],
            [
                'question' => 'Gadai TV',
                'answer' => 'Silahkan informasikan secara detil :

TV merek apa:
Berapa INCH (min. 32 inch):
Kelengkapan (Dusbox dan Remote):',
            ],
            [
                'question' => 'Gadai Drone',
                'answer' => 'Silahkan informasikan secara detil :

Drone Yang Ingin Digadai Apa:
Kelengkapan Apa Saja:',
            ],
            [
                'question' => 'Gadai Game Konsol',
                'answer' => 'Silahkan informasikan secara detil :

Konsol Yang Ingin Digadai Apa:
Kelengkapan Apa Saja:',
            ],
            [
                'question' => 'Gadai Smart Watch',
                'answer' => 'Silahkan informasikan secara detil :

Smart Watch Yang Ingin Digadai Apa:
Kelengkapan Apa Saja:',
            ],
            [
                'question' => 'Gadai tablet',
                'answer' => 'Silahkan informasikan secara detil :

Merk Tablet:
Ram/Internal:
Kelengkapan Apa Saja:
Kondisi Tablet (Minus/Mulus) :',
            ],
            [
                'question' => 'Gadai Emas',
                'answer' => 'Silahkan informasikan secara detil :

emas perhiasan (cincin/kalung/gelang, dsb)/LM:
Kadar:
Berat :',
            ],
            [
                'question' => 'Gadai Aksesoris',
                'answer' => 'Gadai Mulia Menerima Gadai Aksesoris seperti tas Branded dengan Brand Eropa, seperti Dior, Hermes, Louis Vuitton, Gucci, Channel, Balenciaga, Celine, Goyard, Prada, Bottega Venneta, YSL, Fendi, Omega, Cartier, Rolex, Panerai.',
            ],
            [
                'question' => 'Kontak Gadai Aksesoris',
                'answer' => 'Admin Official Gadai Aksesoris https://wa.me/628118389807',
            ],
            [
                'question' => 'Gadai Jam tangan',
                'answer' => 'Gadai Mulia Menerima Gadai Jam tangan branded dengan merek Rolex, Dior, Hermes, Louis Vuitton, Fendi, Omega, Cartier, Rolex, Panerai',
            ],
            [
                'question' => 'Lokasinya Gadai Mulia',
                'answer' => ' Gadai Mulia Bogor
 Gadai Mulia Ciluar Bogor
 Gadai Mulia Depok
 Gadai Mulia Ciawi
 Gadai Mulia Cibubur
 Gadai Mulia Harapan Indah
 Gadai Mulia Jatimekar
 Gadai Mulia Kaliabang Tabrani
 Gadai Mulia Sumber Jaya
 Gadai Mulia Aren Jaya
 Gadai Mulia Cicalengka
 Gadai Mulia Tanjungsari
 Gadai Mulia Rancaekek
 Gadai Mulia Kopo
 Gadai Mulia Ujung Berung
 Gadai Mulia Padasuka Suci
 Gadai Mulia Cinunuk
 Gadai Mulia Garut Suci
 Gadai Mulia Singaparna
 Gadai Mulia Tasikmalaya
 Gadai Mulia Batu Aji
 Gadai Mulia Tanjung Pinang Batu 10
 Gadai Mulia Tanjung Pinang Soekarno Hatta
 Gadai Mulia Bengkong
 Gadai Mulia Tiban
 Gadai Mulia Batam Center
 Gadai Mulia Kebayoran Lama
 Gadai Mulia H.Ten Rawamangun
 Gadai Mulia Blok M Square ',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang H. Ten Rawamangun',
                'answer' => 'Gadai Mulia H.ten Rawamangun / https://maps.app.goo.gl/dNDZ1wn6qgTmnD1n8',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Kebayoran Lama',
                'answer' => 'Gadai Mulia Kebayoran Lama / https://maps.app.goo.gl/SShyojAAaioGGpsZ9',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Blok M',
                'answer' => 'Gadai Mulia Blok M /  https://maps.app.goo.gl/uwM7q2BhLiossNab7',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Harapan Indah',
                'answer' => 'Gadai Mulia Harapan Indah / https://maps.app.goo.gl/dK4Pb25YCm7AuJMs9',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Kaliabang Tabrani',
                'answer' => 'Gadai Mulia Kaliabang Tabrani / https://maps.app.goo.gl/TUNYieT6t5Aj9xgM6',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Aren Jaya',
                'answer' => 'Gadai Mulia Aren Jaya / https://maps.app.goo.gl/jUZWr1pBb6zAvrqX9',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Sumber Jaya',
                'answer' => 'Gadai Mulia Sumber Jaya / https://share.google/vXFXeBvazYt2dEXNa',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Jatimakmur',
                'answer' => 'Gadai Mulia Jatimakmur / https://maps.app.goo.gl/k9z6rcFCJANiQHHQ8',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Cibubur',
                'answer' => 'Gadai Mulia Cibubur / https://maps.app.goo.gl/msVRZHNhPBrdTswh6',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Bogor',
                'answer' => ' Gadai Mulia Bogor / https://maps.app.goo.gl/QiPiBXLyWUBXkf9X7',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Ciluar',
                'answer' => 'Gadai Mulia Ciluar Bogor / https://maps.app.goo.gl/uuWgB3RsebBm86gz5',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Ciawi',
                'answer' => 'Gadai Mulia Ciawi / https://maps.app.goo.gl/C6i4hdP1XrN7sFbq5',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Depok',
                'answer' => 'Gadai Mulia Depok / https://maps.app.goo.gl/mNCoRe5SLbrEMVQQA',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Cinunuk',
                'answer' => 'Gadai Mulia Cinunuk / https://maps.app.goo.gl/5nuGgmDVpxP8KwWF6',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Ujung Berung',
                'answer' => 'Gadai Mulia Ujung Berung / https://maps.app.goo.gl/wPzE3t5KorT36HpRA',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Cicalengka',
                'answer' => 'Gadai Mulia Cicalengka / https://maps.app.goo.gl/rdgnXfsP6b3RKGcD9',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Padasuka Suci',
                'answer' => 'Gadai Mulia Padasuka Suci / https://maps.app.goo.gl/qBeSMqMXwHmdiLXa9',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Kopo',
                'answer' => 'Gadai Mulia Kopo / https://maps.app.goo.gl/LNPZKbi5R17jzRkX8',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Rancaekek',
                'answer' => 'Gadai Mulia Rancaekek / https://maps.app.goo.gl/fVtBMnNoxt837CZK6',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Tanjungsari',
                'answer' => 'Gadai Mulia Tanjungsari / https://maps.app.goo.gl/Tv3Qh2r2hJaauTpv9',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Tasikmalaya',
                'answer' => 'Gadai Mulia Tasikmalaya / https://maps.app.goo.gl/D8EasXudkRhZUCTg7',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Singaparna',
                'answer' => 'Gadai Mulia Singaparna / https://maps.app.goo.gl/vtqz2jg31nVpNdMw9',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Garut',
                'answer' => 'Gadai Mulia Garut Suci / https://maps.app.goo.gl/MQF8VZseLPRG2ou49',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Batam Center',
                'answer' => 'Gadai Mulia Batam Center / https://maps.app.goo.gl/9MRUqVm7nx52hv3d9',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Batam Batu Aji',
                'answer' => 'Gadai Mulia Batam Batu Aji / https://maps.app.goo.gl/mrrw8vRYRuy2a6zk8',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Batam Bengkong',
                'answer' => 'Gadai Mulia Batam Bengkong / https://maps.app.goo.gl/eFosqN6DWiG67Lir5',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Tanjung pinang Batu 10',
                'answer' => 'Gadai Mulia Tj. Pinang Batu 10 / https://maps.app.goo.gl/EqqNKKLP1TEP1jV46',
            ],
            [
                'question' => 'Alamat Gadai Mulia Cabang Tanjung pinang soekarno hatta',
                'answer' => 'Gadai Mulia Tj. Pinang Soekarno Hatta / https://maps.app.goo.gl/MBrFEERUbCN9d8KM',
            ],
            [
                'question' => 'Jam operasional gadai mulia',
                'answer' => 'Baik kak, kami informasikan untuk jam operasional cabang:
* senin s/d jumat           :08:30 - 17:00
* sabtu                           :08:30 - 12:00
* minggu                        :LIBUR/TUTUP
•  Libur Nasional        : Tutup

Jam Operasional Cabang Blok M:
* Senin - Jumat         : 11.00 - 18.00
* Sabtu                    : 11.00 - 18.00
•  Libur Nasional        : Tutup',
            ],
            [
                'question' => 'Barang apa saja yang bisa di gadai',
                'answer' => '1. Emas berupa Logam Mulia (LM) & Perhiasan
2. Elektronik berupa (TV, Laptop, Handphone, Tablet, PS & Drone)
3. Tas & Jam Branded Eropa (Khusus di Cabang Blok M Square)',
            ],
            [
                'question' => 'Apakah ada promo di gadai mulia',
                'answer' => 'https://docs.google.com/spreadsheets/d/1200tVTZdLQj63I5Mw0DUAawaC1Xt81RBy2Pb1p_SoCw/edit?usp=sharing',
            ],
            [
                'question' => 'Saya mau perpanjangan/ penebusan',
                'answer' => 'Untuk informasi detail terkait transaksi, bisa menghubungi nomor cabang tempat kamu bertransaksi ya. Berikut:
- Gadai Mulia Bogor -> 8118389804
- Gadai Mulia Ciluar Bogor -> 8118884750
- Gadai Mulia Depok -> 8118389805
- Gadai Mulia Ciawi -> 81181116024
- Gadai Mulia Cibubur -> 8118389810
- Gadai Mulia Harapan Indah -> 81190049870
- Gadai Mulia Jatimekar -> 8118389803
- Gadai Mulia Kaliabang Tabrani -> 81190049871
- Gadai Mulia Sumber Jaya -> 81119732934
- Gadai Mulia Aren Jaya -> 81188827402
- Gadai Mulia Cicalengka -> 8118125148
- Gadai Mulia Tanjungsari -> 8118125156
- Gadai Mulia Rancaekek -> 81190049868
- Gadai Mulia Kopo -> 81181138019
- Gadai Mulia Ujung Berung -> 8118125146
- Gadai Mulia Padasuka Suci -> 81190035473
- Gadai Mulia Cinunuk -> 81188805362
- Gadai Mulia Garut Suci -> 81190049869
- Gadai Mulia Singaparna -> 81188827392
- Gadai Mulia Tasikmalaya -> 81188805361
- Gadai Mulia Batu Aji -> 8118389817
- Gadai Mulia Tanjung Pinang Batu 10 -> 8118389816
- Gadai Mulia Tanjung Pinang Soekarno Hatta -> 8117704860
- Gadai Mulia Bengkong -> 81188827395
- Gadai Mulia Tiban -> 81170000039
- Gadai Mulia Batam Center -> 8118389812
- Gadai Mulia Kebayoran Lama -> 8118389802
- Gadai Mulia H.Ten Rawamangun -> 8118125135
- Gadai Mulia Blok M Square -> 8118389807',
            ],
            [
                'question' => 'Berapa bunga dan tenor gadai elektronik',
                'answer' => 'Gadai Elektronik jangka waktu pinjaman maksimal 30 hari dengan tenor 8% per  30 hari ',
            ],
            [
                'question' => 'Berapa bunga dan tenor gadai emas',
                'answer' => 'Gadai Emas jangka waktu pinjaman maksimal 120 hari dengan tenor 1,8% per 30 hari',
            ],
            [
                'question' => 'Bagaimana cara gadai di gadai mulia',
                'answer' => 'Berikut Cara Gadai di Gadai Mulia:
1. Membawa E-KTP Asli milik pribadi (tidak boleh diwakilkan)
2. Membawa barang yang ingin di gadai beserta kelengkapannya
3. Datang langsung ke cabang dengan membawa E-KTP dan barang yang akan di gadai. Petugas cabang akan melakukan pengecekan/penaksiran barang gadai
4. Jika simulasi hasil taksiran sudah cocok maka tahap selanjutnya pencairan uang pinjaman
5. Simpan baik-baik lembar surat bukti gadai kamu, untuk penukaran saat proses tebus ataupun perpanjang',
            ],
            [
                'question' => 'Apakah ada lowongan kerja di gadai mulia',
                'answer' => 'Untuk informasi lowongan pekerjaan dapat langsung kunjungi website resmi https://www.gadaimulia.com/career dan segala informasi terbaru tentang lowongan pekerjaan dapat di cek secara berkala',
            ],
            [
                'question' => 'Apakah bisa jemput gadai',
                'answer' => 'Untuk jemput gadai bisa hubungi cabang terdekat:
- Gadai Mulia Bogor -> 8118389804
- Gadai Mulia Ciluar Bogor -> 8118884750
- Gadai Mulia Depok -> 8118389805
- Gadai Mulia Ciawi -> 81181116024
- Gadai Mulia Cibubur -> 8118389810
- Gadai Mulia Harapan Indah -> 81190049870
- Gadai Mulia Jatimekar -> 8118389803
- Gadai Mulia Kaliabang Tabrani -> 81190049871
- Gadai Mulia Sumber Jaya -> 81119732934
- Gadai Mulia Aren Jaya -> 81188827402
- Gadai Mulia Cicalengka -> 8118125148
- Gadai Mulia Tanjungsari -> 8118125156
- Gadai Mulia Rancaekek -> 81190049868
- Gadai Mulia Kopo -> 081181138019
- Gadai Mulia Ujung Berung -> 8118125146
- Gadai Mulia Padasuka Suci -> 81190035473
- Gadai Mulia Cinunuk -> 81188805362
- Gadai Mulia Garut Suci -> 81190049869
- Gadai Mulia Singaparna -> 81188827392
- Gadai Mulia Tasikmalaya -> 81188805361
- Gadai Mulia Batu Aji -> 8118389817
- Gadai Mulia Tanjung Pinang Batu 10 -> 8118389816
- Gadai Mulia Tanjung Pinang Soekarno Hatta -> 8117704860
- Gadai Mulia Bengkong -> 81188827395
- Gadai Mulia Tiban -> 81170000039
- Gadai Mulia Batam Center -> 8118389812
- Gadai Mulia Kebayoran Lama -> 8118389802
- Gadai Mulia H.Ten Rawamangun -> 8118125135
- Gadai Mulia Blok M Square -> 8118389807  ',
            ],
            [
                'question' => 'gadai mulia aman gak',
                'answer' => 'Barang jaminan aman di asuransikan',
            ],
            [
                'question' => 'Gadai Mulia',
                'answer' => 'Gadai Mulia adalah perusahaan pergadaian swasta yang terdaftar dan berizin OJK (Otoritas Jasa Keuangan)',
            ],
            [
                'question' => 'Produk Gadai Mulia',
                'answer' => 'Gadai Mulia terima Gadai Emas perhiasan, logam mulia dan elektronik seperti Hp, Laptop, Kamera, TV dan Drone',
            ],
            [
                'question' => 'Biaya admin Gadai Emas',
                'answer' => 'Biaya admin gadai emas mulai dari Rp 10.000',
            ],
            [
                'question' => 'Biaya admin Gadai Elektronik',
                'answer' => 'Biaya admin gadai elektronik 1% dari nilai pinjaman',
            ],
            [
                'question' => 'masa Gadai Emas',
                'answer' => 'Masa Gadai emas 120 hari',
            ],
            [
                'question' => 'Masa Gadai Elektronik',
                'answer' => 'Masa Gadai emas 30 hari',
            ],
            [
                'question' => 'Biaya denda gadai emas',
                'answer' => 'Biaya denda gadai emas 0,12% per hari',
            ],
            [
                'question' => 'Biaya denda gadai elektronik',
                'answer' => 'Biaya denda gadai elektronik 0,25% per hari',
            ],
            [
                'question' => 'Website gadai mulia',
                'answer' => 'gadaimulia.com',
            ],
            [
                'question' => 'Cabang gadai mulia',
                'answer' => 'Cabang gadai mulia tersebar di wilayah Jakarta, Jawa Barat dan Kepulauan Riau',
            ],
            [
                'question' => 'Gadai Aksesoris',
                'answer' => 'Merupakan produk gadai menggunakan jaminan berupa Barang Branded dengan Brand Eropa, seperti Tas, dompet, Belt, dengan brand seperti Dior, Hermes, Louis Vuitton, Gucci, Channel, Balenciaga, Celine, dan lainnya. Pencairan yang diberikan kepada nasabah dengan tenor maksimal 30 hari dan dapat diperpanjang, pemberian pinjaman diberikan dengan cepat dan sewa modal yang lebih ringan dan aman. Nasabah hanya perlu membawa jaminan berupa Barang Branded dengan Brand Eropa, seperti Tas, dompet, Belt, dengan brand seperti Dior, Hermes, Louis Vuitton, Gucci, Channel, Balenciaga, Celine, dan lain-lain.',
            ],
            [
                'question' => 'Gadai Cicilan Emas',
                'answer' => 'Merupakan produk Gadai Cicilan Emas menggunakan jaminan berupa emas perhiasan atau logam mulia. Pinjaman yang diberikan kepada nasabah dengan jangka waktu yang lebih panjang serta dapat dilakukan penebusan cicilan. Pemberian pinjaman diberikan dengan cepat dan sewa modal yang lebih ringan dan aman. Nasabah hanya perlu membawa agunan berupa emas perhiasan & Logam Mulia.',
            ],
            [
                'question' => 'Gadai Elektronik',
                'answer' => 'Merupakan produk gadai menggunakan jaminan berupa barang elektronik (Laptop, Hp, Kamera, TV LED, Drone, Game Console). Pinjaman yang diberikan kepada nasabah dengan jangka waktu maksimal 30 hari, Pemberian pinjaman diberikan dengan cepat, sewa modal yang lebih ringan dan aman. Nasabah hanya perlu membawa agunan berupa barang elektronik (Laptop, HP, PC (iMAC), Drone, Kamera, TV) dengan kondisi apapun.',
            ],
            [
                'question' => 'Gadai Mulia',
                'answer' => 'Gadai Mulia adalah Perusahaan Pergadaian Swasta yang berizin dan diawasi oleh OJK. Berdiri sejak Agustus 2018. Yang telah berizin dan diawasi oleh OJK melalui Keputusan Dewan Komisioner KEP-41/NB.1/2019 tanggal 18 November 2019 untuk wilayah Jawa Barat, kemudian KEP158/NB.1/2020 pada tanggal 24 November 2020 untuk wilayah Kepulauan Riau dan KEP-10/NB.1/2022 pada tanggal 11 Februari 2022 untuk wilayah DKI Jakarta, menjadikan Gadai Mulia sebagai perusahaan Gadai Resmi dan Terpercaya. Gadai Mulia memberikan jasa atau layanan keuangan melalui penyaluran dana pinjaman kepada masyarakat dengan jaminan berupa Logam Mulia, Perhiasan Emas, Elektronik, Kendaraan Bermotor, dan barang Gudang lainnya. Melalui beragam produk dan layanan istimewa Gadai Mulia memberikan pelayanan dan kemudahan kepada masyarakat khususnya Solusi Jitu untuk kebutuhan dana cepat.Gadai Mulia adalah Perusahaan Pergadaian Swasta yang berizin dan diawasi oleh OJK',
            ],
            [
                'question' => 'Lelang Gadai Mulia',
                'answer' => 'Gadai Mulia tidak pernah melakukan lelang terbuka di cabang, kegiatan lelang gadai mulia di selenggarakan oleh Balai Lelang Harmoni dan kantor pusat Gadai Mulia. apabila hati-hati penipuan mengatas-namakan lelang Gadai Mulia',
            ],
            [
                'question' => 'Sosial media Gadai Mulia',
                'answer' => 'https://instagram.com/gadaimulia',
            ],
            [
                'question' => 'Lowongan kerja Gadai Mulia',
                'answer' => 'https://gadaimulia.com/career',
            ],
        ];

        foreach ($data as $row) {
            FaqChatbotQna::create($row);
        }
    }
}
