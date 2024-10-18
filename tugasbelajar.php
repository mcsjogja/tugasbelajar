<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\AnjunganModel;
use App\Models\AsnModel;
use App\Models\DashboardModel;
use App\Models\PgModel;
use App\Models\TubelModel;
use App\Models\UserModel;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
use NcJoes\OfficeConverter\OfficeConverter;
use PHPUnit\Framework\Constraint\Count;

class TugasBelajar extends BaseController
{
    protected $berkas = FCPATH . 'berkasdigital/';
    protected $template = FCPATH . 'template/tubel/';

    public function __construct()
    {
        $this->currentuser = session()->get('logged_in');
        $this->user_ds     = session()->get('user_dashboard');
        $this->asn         = new AsnModel;
        $this->user        = new UserModel;
        $this->anjungan    = new AnjunganModel;
        $this->tubel       = new TubelModel;
        $this->pg          = new PgModel;
        $this->dashboard   = new DashboardModel;
    }

    public function index()
    {
        $now = date('Y-m-d');
    }
    
    // operator 
    public function operator()
    {
        $unor    = session()->unor;
        if (is_null($unor)) :
            return redirect()->back()->with('warning', ['Tidak ada unit kerja yang disetting. Silahkan hubungi Admin kami.']);
        endif;

        $ls = $this->tubel->getListPns($unor);
        $data['list'] = $ls;
        return view('dashboard/layanan/tubel/operator', $data);
    }
    public function unduh_rekomendasi($id)
    {

        $id = decry($id);
        if (!$id) :
            return redirect()->back()->with('warning', ['Belum dipilih']);
        endif;

        $tubel = $this->tubel->getUsulById($id);
        if (count($tubel) != '1') :
            return redirect()->back()->with('warning', ['Cek lagi ']);
        endif;

        $nip = $tubel[0]['pns_nip'];
        $asn = $this->asn->getByNip($nip);
        if (count($asn) != '1') :
            return redirect()->back()->with('message', ['Data ASN nonaktif, silahkan hubungi BKPPD ']);
        endif;

        $data['tubel'] = $tubel[0];
        $data['asn']   = $asn[0];

        $template  = new TemplateProcessor($this->template . "02_format_rekomendasi_opd_aktif.docx"); // semua format aktif

        $unor = substr($asn[0]['unor_kode'], 0, 2);
        if ($unor == '12' || $unor == '13') :
            $unor = '11';
        elseif ($unor == '09') :
            $unor = '08';
        else :
            $unor = $unor;
        endif;

        $ka = $this->asn->getKepala($unor);

        $ka_opd = "[Tulis Nama Kepala Perangkat Daerah]";
        $ka_nip = "[Tulis NIP Baru]";
        $ka_jabatan = "[Tulis Jabatan]";
        $ka_unitkerja = "[Tulis Unit Kerja / Perangkat Daerah]";
        if (count($ka) == '1') :
            $ka_opd = $ka[0]['pemangku_nama'];
            $ka_nip = $ka[0]['pemangku_nipbaru'];
            $ka_jabatan = $ka[0]['unor_jabatan'];
            $ka_unitkerja = $ka[0]['unor_nama'];
        endif;

        $template->setValue('ka_opd', $ka_opd);
        $template->setValue('ka_nip', $ka_nip);
        $template->setValue('ka_jabatan', $ka_jabatan);
        $template->setValue('ka_unitkerja', $ka_unitkerja);

        // pns
        $template->setValue('pns_nama', $asn[0]['pns_namalengkap']);
        $template->setValue('pns_nip', $asn[0]['pns_nipbaru']);
        $template->setValue('pns_pangkat', $asn[0]['golru_pangkat']);
        $template->setValue('pns_golongan', $asn[0]['golru_nama']);
        $template->setValue('pns_jabatan', $asn[0]['pns_ketjabatan']);
        // pns

        // lembaga
        $template->setValue('prodi', $tubel[0]['prodi']);
        $template->setValue('tkpendidikan', $tubel[0]['tkpendidikan_nama']);
        $template->setValue('lembaga', $tubel[0]['namasekolah']);
        $template->setValue('mulai', @toindo($tubel[0]['mulai']));
        $template->setValue('akhir', @toindo($tubel[0]['akhir']));
        $template->setValue('akreditasi', $tubel[0]['akreditasi']);
        $template->setValue('sumber_biaya', ucfirst($tubel[0]['pembiayaan']));
        $template->setValue('printed', toindo(date('Y-m-d')));
        // lembaga


        $nipbaru = $asn[0]['pns_nipbaru'];
        $nama    = $asn[0]['pns_nama'];
        $nama    = substr($nama, 0, 10);
        $nama    = str_replace(' ', '', $nama);
        $nama    = str_replace(',', '', $nama);
        $nama    = str_replace('.', '', $nama);

        $namafile = "rekom_tb_" . $nama . '_' . $nipbaru . '.docx';

        $outfile   = $this->template . 'generate/' . $namafile;
        $template->saveAs($outfile);

        return $this->response->download($outfile, null); //->setFileName($nameunduh);
    }

    public function unduh_penolakan($id)
    {

        $id = decry($id);
        if (!$id) :
            return redirect()->back()->with('warning', ['Belum dipilih']);
        endif;

        $tubel = $this->tubel->getUsulById($id);
        if (count($tubel) != '1') :
            return redirect()->back()->with('warning', ['Cek lagi ']);
        endif;

        $nip = $tubel[0]['pns_nip'];
        $asn = $this->asn->getByNip($nip);
        if (count($asn) != '1') :
            return redirect()->back()->with('message', ['Data ASN nonaktif, silahkan hubungi BKPPD ']);
        endif;

        $data['tubel'] = $tubel[0];
        $data['asn']   = $asn[0];

        $template  = new TemplateProcessor($this->template . "03_format_tolak_pd.docx"); // semua format aktif

        $unor = substr($asn[0]['unor_kode'], 0, 2);
        if ($unor == '12' || $unor == '13') :
            $unor = '11';
        elseif ($unor == '09') :
            $unor = '08';
        else :
            $unor = $unor;
        endif;

        $ka = $this->asn->getKepala($unor);

        $ka_opd = "[Tulis Nama Kepala Perangkat Daerah]";
        $ka_nip = "[Tulis NIP Baru]";
        $ka_jabatan = "[Tulis Jabatan]";
        $ka_unitkerja = "[Tulis Unit Kerja / Perangkat Daerah]";
        if (count($ka) == '1') :
            $ka_opd = $ka[0]['pemangku_nama'];
            $ka_nip = $ka[0]['pemangku_nipbaru'];
            $ka_jabatan = $ka[0]['unor_jabatan'];
            $ka_unitkerja = $ka[0]['unor_nama'];
        endif;

        $template->setValue('ka_opd', $ka_opd);
        $template->setValue('ka_nip', $ka_nip);
        $template->setValue('ka_jabatan', $ka_jabatan);
        $template->setValue('ka_unitkerja', $ka_unitkerja);

        // pns
        $template->setValue('pns_nama', $asn[0]['pns_namalengkap']);
        $template->setValue('pns_nip', $asn[0]['pns_nipbaru']);
        $template->setValue('pns_pangkat', $asn[0]['golru_pangkat']);
        $template->setValue('pns_golongan', $asn[0]['golru_nama']);
        $template->setValue('pns_jabatan', $asn[0]['pns_ketjabatan']);
        // pns

        // lembaga
        $template->setValue('prodi', $tubel[0]['prodi']);
        $template->setValue('tkpendidikan', $tubel[0]['tkpendidikan_nama']);
        $template->setValue('lembaga', $tubel[0]['namasekolah']);
        $template->setValue('mulai', @toindo($tubel[0]['mulai']));
        $template->setValue('akhir', @toindo($tubel[0]['akhir']));
        $template->setValue('akreditasi', $tubel[0]['akreditasi']);
        $template->setValue('sumber_biaya', ucfirst($tubel[0]['pembiayaan']));
        $template->setValue('printed', toindo(date('Y-m-d')));
        // lembaga


        $nipbaru = $asn[0]['pns_nipbaru'];
        $nama    = $asn[0]['pns_nama'];
        $nama    = substr($nama, 0, 10);
        $nama    = str_replace(' ', '', $nama);
        $nama    = str_replace(',', '', $nama);
        $nama    = str_replace('.', '', $nama);

        $namafile = "tolak_tb_" . $nama . '_' . $nipbaru . '.docx';

        $outfile   = $this->template . 'generate/' . $namafile;
        $template->saveAs($outfile);

        return $this->response->download($outfile, null); //->setFileName($nameunduh);
    }

    public function unduh_pernyataan($id)
    {

        $id = decry($id);
        if (!$id) :
            return redirect()->back()->with('warning', ['Belum dipilih']);
        endif;

        $tubel = $this->tubel->getUsulById($id);
        if (count($tubel) != '1') :
            return redirect()->back()->with('warning', ['Cek lagi ']);
        endif;

        $nip = $tubel[0]['pns_nip'];
        $asn = $this->asn->getByNip($nip);
        if (count($asn) != '1') :
            return redirect()->back()->with('message', ['Data ASN nonaktif, silahkan hubungi BKPPD ']);
        endif;

        $data['tubel'] = $tubel[0];
        $data['asn']   = $asn[0];

        $template  = new TemplateProcessor($this->template . "04_format_pernyataan_3_poin.docx"); // semua format aktif

        $unor = substr($asn[0]['unor_kode'], 0, 2);
        if ($unor == '12' || $unor == '13') :
            $unor = '11';
        elseif ($unor == '09') :
            $unor = '08';
        else :
            $unor = $unor;
        endif;

        $ka = $this->asn->getKepala($unor);

        $ka_opd = "[Tulis Nama Kepala Perangkat Daerah]";
        $ka_nip = "[Tulis NIP Baru]";
        $ka_jabatan = "[Tulis Jabatan]";
        $ka_unitkerja = "[Tulis Unit Kerja / Perangkat Daerah]";
        if (count($ka) == '1') :
            $ka_opd = $ka[0]['pemangku_nama'];
            $ka_nip = $ka[0]['pemangku_nipbaru'];
            $ka_jabatan = $ka[0]['unor_jabatan'];
            $ka_unitkerja = $ka[0]['unor_nama'];
        endif;

        $template->setValue('ka_opd', $ka_opd);
        $template->setValue('ka_nip', $ka_nip);
        $template->setValue('ka_jabatan', $ka_jabatan);
        $template->setValue('ka_unitkerja', $ka_unitkerja);

        // pns
        $template->setValue('pns_nama', $asn[0]['pns_namalengkap']);
        $template->setValue('pns_nip', $asn[0]['pns_nipbaru']);
        $template->setValue('pns_pangkat', $asn[0]['golru_pangkat']);
        $template->setValue('pns_golongan', $asn[0]['golru_nama']);
        $template->setValue('pns_jabatan', $asn[0]['pns_ketjabatan']);
        // pns

        // lembaga
        $template->setValue('prodi', $tubel[0]['prodi']);
        $template->setValue('tkpendidikan', $tubel[0]['tkpendidikan_nama']);
        $template->setValue('lembaga', $tubel[0]['namasekolah']);
        $template->setValue('mulai', @toindo($tubel[0]['mulai']));
        $template->setValue('akhir', @toindo($tubel[0]['akhir']));
        $template->setValue('akreditasi', $tubel[0]['akreditasi']);
        $template->setValue('sumber_biaya', ucfirst($tubel[0]['pembiayaan']));
        $template->setValue('printed', toindo(date('Y-m-d')));
        // lembaga


        $nipbaru = $asn[0]['pns_nipbaru'];
        $nama    = $asn[0]['pns_nama'];
        $nama    = substr($nama, 0, 10);
        $nama    = str_replace(' ', '', $nama);
        $nama    = str_replace(',', '', $nama);
        $nama    = str_replace('.', '', $nama);

        $namafile = "pernyataan_" . $nama . '_' . $nipbaru . '.docx';

        $outfile   = $this->template . 'generate/' . $namafile;
        $template->saveAs($outfile);

        return $this->response->download($outfile, null); //->setFileName($nameunduh);
        //unlink($this->template . $namafile);
    }

    public function keputusan($id)
    {
        $id = decry($id);
        if (!$id) :
            return redirect()->back()->with('warning', ['Belum dipilih']);
        endif;

        $tubel = $this->tubel->getUsulById($id);
        if (count($tubel) != '1') :
            return redirect()->back()->with('warning', ['Cek lagi ']);
        endif;

        $data['tubel'] = $tubel[0];
        return view("dashboard/layanan/tubel/operator_keputusan", $data);
    }
    public function keputusan_save()
    {
        $post               = sanitize($this->request->getPost());

        if ($post['hasil_opd'] == '-') :
            return redirect()->back()->with('warning', ['Hasil Keputusan Belum Dipilih']);
        endif;

        $rules              = "uploaded[keputusan_opd]|mime_in[keputusan_opd,application/pdf]|max_size[keputusan_opd,700]";
        $pernyatan_wajib    = "uploaded[pernyataan]|mime_in[pernyataan,application/pdf]|max_size[pernyataan,700]";
        $pernyatan_tdkwajib = "mime_in[pernyataan,application/pdf]|max_size[pernyataan,700]";
        $max                = "Ukuran File Maksimal 700 Kb";

        $hasil = $post['hasil_opd']; // tolak / setuju
        if ($hasil == 'tolak') :
            $message = 'Kepala Perangkat Daerah menolak / tidak memberikan rekomendasi proses pengajuan Tugas Belajar';
            $rules_pernyataan = $pernyatan_tdkwajib;
        else :
            $message = 'Kepala Perangkat Daerah menyetujui dan memberikan rekomendasi proses pengajuan Tugas Belajar';
            $rules_pernyataan = $pernyatan_wajib;
        endif;

        // baru
        $input = $this->validate([
            'keputusan_opd' => [
                'rules'  => $rules,
                'errors' => [
                    'uploaded' => 'Harus Ada File Keputusan yang diupload',
                    'mime_in'  => 'Berkas Digital Harus Berupa PDF',
                    'max_size' => $max
                ]
            ],
            'pernyataan' => [
                'rules'  => $rules_pernyataan,
                'errors' => [
                    'uploaded' => 'Harus Ada File Surat Pernyataan yang diupload',
                    'mime_in'  => 'Berkas Digital Harus Berupa PDF',
                    'max_size' => $max
                ]
            ]

        ]);

        //dd($this->request->getFiles());

        $file               = $this->request->getFile('keputusan_opd');
        $file_pernyataan    = $this->request->getFile('pernyataan');
        // if($file_pernyataan =='') :
        //     print_r('tidak unggah');
        // endif;
        // dd($file_pernyataan);
        $dir                = $this->berkas . 'usulan/tubel/' . $post['usul_id'] . '/';

        $new_name            = 'keputusan_kapd_tubel.pdf';
        $new_name_pernyataan = 'pernyataan_tubel.pdf';

        if (!$input) {
            $err = $this->validator->getErrors();
            $er  = '';
            foreach ($err as $v) :
                $er .= $v . "<br>";
            endforeach;

            return redirect()->back()->with('warning', [$er]);
        } else {

            $file->move($dir, $new_name, true);

            if($file_pernyataan !='') :
                $file_pernyataan->move($dir, $new_name_pernyataan, true);
            endif;

            $post['rekomendasi_opd']     = 'Y';
            $post['rekomendasi_opd_tgl'] = date('Y-m-d H:i:s');

            $status = [
                'usul_id' => $post['usul_id'],
                'refstatus_id' => '9',
                'usulstatus_tgl' => date('Y-m-d H:i:s'),
                'catatan' => $message
            ];

            @$this->tubel->updateUsulan($post);
            @$this->anjungan->addStatus($status);

            $to = base_url('dashboard/tugas-belajar/operator-pd');
            return redirect()->to($to)->with('message', ['Berhasil Mengirim Berkas']);
        }
    }
    public function open_usulan($id)
    {
        $id = decry($id);
        if (!$id) :
            return redirect()->back()->with('warning', ['Periksa usulan ']);
        endif;

        $rw          = $this->tubel->getUsulById($id);
        $pns_nip     = $rw[0]['pns_nip'];

        $data['usul_id']      = $id;
        $data['pns']          = $this->asn->getByNip($pns_nip)[0];
        $data['detail']       = $rw;
        $data['berkas']       = $this->berkas;
        $data['tkpendidikan'] = $this->asn->getTkPendidikanBkn();
        $data['syarat']       = $this->tubel->getSyarat('pg');

        return view('dashboard/layanan/tubel/operator_open', $data);
    }
    // end operator 

    // verifikator
    public function verifikator($id = null)
    {
        $petugas = $this->user_ds;
        $to  = base_url('dashboard/tugas-belajar/verifikator');

        if (is_null($id)) :
            $data['info']    = $this->infoVerifikasiku($petugas);
            //dd($data);
            return view('dashboard/layanan/tubel/verifikator', $data);
        else :
            $id = decry($id);
            if (!$id) :
                return redirect()->to($to);
            endif;

            $cekvku = $this->tubel->verifikasiCek($id, $petugas);  // apakah verifikasiku atau bukan ?

            if (count($cekvku) == '1') :

                // tampilkan data untuk diverifikasi                
                $usulan         = $cekvku;
                $syarat         = $this->tubel->getSyarat();

                $data['usul_id']      = $id;
                $data['pns']          = $this->asn->getByNip($usulan[0]['pns_nip'])[0];

                $data['usulan'] = $usulan[0];
                $data['syarat'] = $syarat;
                $data['berkas'] = $this->berkas;
                //$data['detail'] = $this->tubel->getDetail($usulan[0]['usul_id']); //getUsulById
                $data['detail'] = $this->tubel->getUsulById($usulan[0]['usul_id']); //getUsulById
                $data['tkpendidikan'] = $this->pg->getTkPendidikan(); // ambil dari model pg gpp
                // ------------------------------------------------------
                //dd($data);

                return view('dashboard/layanan/tubel/verifikator_open', $data);

            else :
                $cek = $this->tubel->verifikasiCek($id);  // cek apakah sudah dipilih verifikator lain ?

                if (count($cek) == '1') {   // kosong siap dipilih                             
                    $update = ['verifikator' => $petugas];
                    $this->tubel->updateusulan_tbl($id, $update); // update nama verifikator              
                    return redirect()->to("dashboard/tugas-belajar/verifikasi/pilih/" . encry($id));
                } else {
                    return redirect()->back()->with('warning', ['Sudah dipilih verifikator lain !']);
                }
            endif;

        endif;
    }

    public function infoVerifikasiku($petugas)
    {
        $all          = $this->tubel->allTubel();
        $inbox_opd    = $this->tubel->inboxOPD();
        $baru         = $this->tubel->kirimanBaru();
        $tolakopd     = $this->tubel->tolakOpd();
        $revisi       = $this->tubel->revisiBaru($petugas);
        $vku          = $this->tubel->verifikasiku($petugas);

        $btl        = carian($vku, 'verifikator_hasil', 'btl');

        $ms1        = carian($vku, 'verifikator_hasil', 'ms');
        $ms2        = carian($ms1, 'supervisi_hasil', 'setuju');

        $ms_tolak   = carian($ms1, 'supervisi_hasil', 'tolak');
        $ms_tunggu  = carian($ms1, 'supervisi_hasil', '-');

        $tms1       = carian($vku, 'verifikator_hasil', 'tms');
        $tms2       = carian($tms1, 'supervisi_hasil', 'setuju');

        $tms_tolak  = carian($tms1, 'supervisi_hasil', 'tolak');
        $tms_tunggu = carian($tms1, 'supervisi_hasil', '-');

        $menunggu_verifikasi = carian($vku, 'verifikator_hasil', '-');

        // tambahan 
        $belum_terbit_rekom_seleksi = carian($ms2, 'rekomendasi_seleksi', 'N');
        $sudah_terbit_rekom_seleksi = carian($ms2, 'rekomendasi_seleksi', 'Y');

        $unggah_lulus        = carian($sudah_terbit_rekom_seleksi, 'unggah_lulus', 'Y');
        //

        $info = [
            'all'           => $all,
            'jml'           => $vku,
            'btl'           => $btl,
            'ms'            => $ms2,
            'ms_tolak'      => $ms_tolak,
            'ms_tunggu'     => $ms_tunggu,
            'tms'           => $tms2,
            'tms_tolak'     => $tms_tolak,
            'tms_tunggu'    => $tms_tunggu,
            'menunggu_keputusan_verifikasi' => $menunggu_verifikasi,
            'baru' => $baru,
            'tolak_opd'=> $tolakopd,
            'revisi' => $revisi,
            'inbox_opd' => $inbox_opd,
            'belum_terbit_rekom_seleksi' => $belum_terbit_rekom_seleksi,
            'sudah_terbit_rekom_seleksi' => $sudah_terbit_rekom_seleksi,
            'unggah_lulus' => $unggah_lulus
        ];
        return $info;
    }

    public function verifikasi_hasil($jenis)
    {
        $petugas      = $this->user_ds;
        $info         = $this->infoVerifikasiku($petugas);
        $data['info'] = $info;

        if ($jenis == 'ms') :
            $result = $info['ms'];
            $judul  = "Memenuhi Syarat";
        elseif ($jenis == 'ms-tolak') :
            $result = $info['ms_tolak'];
            $judul  = "Memenuhi Syarat -> DITOLAK";
        elseif ($jenis == 'ms-menunggu') :
            $result = $info['ms_tunggu'];
            $judul  = "Memenuhi Syarat -> BELUM DISUPERVISI";
        elseif ($jenis == 'tms') :
            $result = $info['tms'];
            $judul  = "Tidak Memenuhi Syarat";
        elseif ($jenis == 'tms-tolak') :
            $result = $info['tms_tolak'];
            $judul  = "Tidak Memenuhi Syarat -> DITOLAK";
        elseif ($jenis == 'tms-menunggu') :
            $result = $info['tms_tunggu'];
            $judul  = "Tidak Memenuhi Syarat -> BELUM DISUPERVISI";
        elseif ($jenis == 'belum-selesai') :
            $result = $info['menunggu_keputusan_verifikasi'];
            $judul  = "Belum kamu selesaikan";
        elseif ($jenis == 'btl') :
            $result = $info['btl'];
            $judul  = "Berkas Tidak Lengkap";
        elseif ($jenis == 'all') :
            $result = $info['all'];
            $judul  = "Semua Usulan";
        elseif ($jenis == 'inbox') :
            $result = $info['jml'];
            $judul  = "Semua Yang Kamu Verifikasi";
        elseif ($jenis == 'revisi') :
            $result = $info['revisi'];
            $judul  = "Revisi";
        elseif ($jenis == 'belum-dikirim') :
            $result = $info['belum_proses'];
            $judul  = "Belum dikirim dan diproses lebih lanjut !";
        elseif ($jenis == 'dikirim') :
            $result = $info['dikirim'];
            $judul  = "Dikirim ke BKN ";
        elseif ($jenis == 'belum-terbit-rekom-seleksi') :
            $result = $info['belum_terbit_rekom_seleksi'];
            $judul  = "Belum Terbit Rekomendasi Seleksi";
        elseif ($jenis == 'sudah-terbit-rekom-seleksi') :
            $result = $info['sudah_terbit_rekom_seleksi'];
            $judul  = "Sudah Terbit Rekomendasi Seleksi";
        elseif ($jenis == 'unggah-lulus') :
            $result = $info['unggah_lulus'];
            $judul  = "Sudah Unggah LULUS SELEKSI / DITERIMA";
        else :
            $result = $info['baru'];
            $judul  = 'Usulan Baru';
        endif;

        $data['jenis']  = $jenis;
        $data['judul']  = $judul;
        $data['result'] = $result;

        if ($jenis == 'belum-terbit-rekom-seleksi' || $jenis == 'sudah-terbit-rekom-seleksi' || $jenis == 'unggah-lulus') :
            return view('dashboard/layanan/tubel/verifikator_rekomseleksi', $data);
        else :
            return view('dashboard/layanan/tubel/verifikator_hasil', $data);
        endif;

        // if ($jenis != 'belum-dikirim') :
        //     return view('dashboard/layanan/tubel/verifikator_hasil', $data);
        // else :
        // return view('dashboard/layanan/pg/vr_check_belum_dikirim', $data);
        // endif;

    }
    public function verifikasi_save()
    {
        $post                    = sanitize($this->request->getPost());
        $usul_id                 = $post['usul_id'];
        $post['verifikator_tgl'] = date('Y-m-d H:i:s');

        unset($post['usul_id']);

        if ($post['verifikator_hasil'] == '-') :
            return redirect()->back()->with('warning', ['Belum menentukan keputusan !']);
        elseif ($post['verifikator_hasil'] != 'ms' && $post['verifikator_catatan'] == '') :
            return redirect()->back()->with('warning', ['Isi catatan !']);
        endif;

        // reset supervisi
        $post['supervisi_hasil'] = '-';
        $post['supervisi_tgl'] = null;
        // end reset supervisi

        $do = $this->tubel->do_verifikasi($usul_id, $post);
        if ($do) :
            return redirect()->to("dashboard/tugas-belajar/verifikasi")->with('message', ['Berhasil verifikasi !']);
        else :
            return redirect()->back()->with('warning', ['Gagal verifikasi !']);
        endif;
    }

    public function rekom_seleksi($id)
    {
        $id             = decry($id);
        $usulan         = $this->tubel->getUsulById($id);
        $petugas        = $this->user_ds;
        $info           = $this->infoVerifikasiku($petugas);

        $data['info']       = $info;
        $data['usulan']     = $usulan[0];
        $data['berkas']     = $this->berkas;
        $data['template']   = $this->template;

        $posisi = 'belum-terbit-rekom';
        if ($usulan[0]['rekomendasi_seleksi'] == 'Y') :
            $posisi = 'rekom-seleksi';
        endif;
        if ($usulan[0]['unggah_lulus'] == 'Y') :
            $posisi = 'unggah-lulus';
        endif;

        $data['posisi'] = $posisi;

        return view('dashboard/layanan/tubel/rekom_seleksi', $data);
    }
    public function generate_rekom_seleksi($id)
    {
        $id             = decry($id);
        if (!$id) redirect()->back()->with('warning', ['Pilih Usulan']);

        $usulan        = $this->tubel->getUsulById($id);
        $nip = $usulan[0]['pns_nip'];
        $asn = $this->asn->getByNip($nip);
        if (count($asn) != '1') :
            return redirect()->back()->with('message', ['Data ASN nonaktif, silahkan hubungi BKPPD ']);
        endif;

        //
        $template  = new TemplateProcessor($this->template . "05_format_rekomendasi_seleksi.docx");

        // pns
        $template->setValue('pns_nama', $asn[0]['pns_namalengkap']);
        $template->setValue('pns_nip', $asn[0]['pns_nipbaru']);
        $template->setValue('pns_pangkat', $asn[0]['golru_pangkat']);
        $template->setValue('pns_golongan', $asn[0]['golru_nama']);
        $template->setValue('pns_jabatan', $asn[0]['pns_ketjabatan']);
        $template->setValue('pns_unitkerja', $asn[0]['pns_ketopd']);
        // pns

        // lembaga
        $template->setValue('prodi', $usulan[0]['prodi']);
        $template->setValue('tkpendidikan', $usulan[0]['tkpendidikan_nama']);
        $template->setValue('lembaga', $usulan[0]['namasekolah']);
        $template->setValue('akreditasi', $usulan[0]['akreditasi']);
        $template->setValue('sumber_biaya', ucfirst($usulan[0]['pembiayaan']));
        // lembaga


        $usul_id = $usulan[0]['usul_id'];
        $nipbaru = $asn[0]['pns_nipbaru'];
        $nama    = $asn[0]['pns_nama'];
        $nama    = substr($nama, 0, 10);
        $nama    = str_replace(' ', '', $nama);
        $nama    = str_replace(',', '', $nama);
        $nama    = str_replace('.', '', $nama);

        //$namafile  = "rekom_seleksi_tb_" . $nama . '_' . $nipbaru . '.docx';
        $namafile  = "rekom_seleksi_tb_".$usul_id.'_'. $nama . '_' . $nipbaru . '.docx';
        $outfile   = $this->template . 'generate/' . $namafile;

        $template->saveAs($outfile);


        // update rekom seleksi
           $update['usul_id'] = $usul_id;
           $update['rekomendasi_seleksi']     = 'Y';
           //$update['rekomendasi_seleksi_tgl'] = date('Y-m-d H:i:s');
           @$this->tubel->updateUsulan($update); 
        // end update rekom

        return $this->response->download($outfile, null); //->setFileName($nameunduh);
        //
    }
    public function generate_sk($id)
    {
        $id             = decry($id);
        if (!$id) redirect()->back()->with('warning', ['Pilih Usulan']);

        $usulan        = $this->tubel->getUsulById($id);
        $nip = $usulan[0]['pns_nip'];
        $asn = $this->asn->getByNip($nip);
        if (count($asn) != '1') :
            return redirect()->back()->with('message', ['Data ASN nonaktif, silahkan hubungi BKPPD ']);
        endif;

        $bebas   = $usulan[0]['isbebas'];
        $tmp_sk  = "06_format_sk_aktif.docx";
        //
        if ($bebas == 'Y') :
            $tmp_sk  = "06_format_sk_diberhentikan.docx";
        endif;
        $tgllahir = toindo($asn[0]['pns_tanggallahir']);

        //
        $template  = new TemplateProcessor($this->template . $tmp_sk);

        // pns
        $template->setValue('pns_nama', $asn[0]['pns_namalengkap']);
        $template->setValue('pns_nip', $asn[0]['pns_nipbaru']);
        $template->setValue('pangkat', $asn[0]['golru_pangkat']);
        $template->setValue('golongan', $asn[0]['golru_nama']);
        $template->setValue('pns_tempatlahir', $asn[0]['pns_tempatlahir']);
        $template->setValue('pns_tgllahir', $tgllahir);
        $template->setValue('pns_jabatan', $asn[0]['pns_ketjabatan']);
        $template->setValue('pns_unitkerja', $asn[0]['pns_ketopd']);
        // pns

        // lembaga
        $template->setValue('prodi', $usulan[0]['prodi']);
        $template->setValue('tkpendidikan', $usulan[0]['tkpendidikan_nama']);
        $template->setValue('lembaga', $usulan[0]['namasekolah']);
        $template->setValue('akreditasi', $usulan[0]['akreditasi']);
        $template->setValue('sumber_biaya', ucfirst($usulan[0]['pembiayaan']));
        $template->setValue('awal', @toindo($usulan[0]['mulai']));
        $template->setValue('akhir', @toindo($usulan[0]['akhir']));
        // lembaga

        $nipbaru   = $asn[0]['pns_nipbaru'];
        $nama      = $asn[0]['pns_nama'];
        $nama      = substr($nama, 0, 10);
        $nama      = str_replace(' ', '', $nama);
        $nama      = str_replace(',', '', $nama);
        $nama      = str_replace('.', '', $nama);

        $namafile  = "sk_tubel_" . $nama . '_' . $nipbaru . '.docx';
        $outfile   = $this->template . 'generate/' . $namafile;

        $template->saveAs($outfile);

        return $this->response->download($outfile, null); //->setFileName($nameunduh);
        //
    }
    public function unggah_rekom_seleksi()
    {
        $post = $this->request->getPost();

        $rules              = "uploaded[file]|mime_in[file,application/pdf]|max_size[file,700]";
        $max                = "Ukuran File Maksimal 700 Kb";

        // baru
        $input = $this->validate([
            'file' => [
                'rules'  => $rules,
                'errors' => [
                    'uploaded' => 'Harus Ada File Keputusan yang diupload',
                    'mime_in'  => 'Berkas Digital Harus Berupa PDF',
                    'max_size' => $max
                ]
            ]
        ]);

        $file               = $this->request->getFile('file');
        $dir                = $this->berkas . 'usulan/tubel/' . $post['usul_id'] . '/';
        $new_name           = $post['namafile'] . '.pdf';

        if (!$input) {
            $err = $this->validator->getErrors();
            $er  = '';
            foreach ($err as $v) :
                $er .= $v . "<br>";
            endforeach;

            return redirect()->back()->with('warning', [$er]);
        } else {

            $file->move($dir, $new_name, true);

            unset($post['namafile']);
            $post['rekomendasi_seleksi']     = 'Y';
            $post['rekomendasi_seleksi_tgl'] = date('Y-m-d H:i:s');

            $status = [
                'usul_id'        => $post['usul_id'],
                'refstatus_id'   => '10', // diterbitkan surat rekomendasi
                'usulstatus_tgl' => date('Y-m-d H:i:s'),
                'catatan'        => 'Admin Mengunggah Rekomendasi Seleksi, silahkan diunduh'
            ];

            @$this->tubel->updateUsulan($post);
            @$this->anjungan->addStatus($status);

            $to = base_url('dashboard/tugas-belajar/verifikasi');
            return redirect()->to($to)->with('message', ['Berhasil Mengirim Berkas']);
        }
    }

    public function unggah_sk_tubel()
    {
        $post = $this->request->getPost();

        $rules              = "uploaded[file]|mime_in[file,application/pdf]|max_size[file,700]";
        $max                = "Ukuran File Maksimal 700 Kb";

        // baru
        $input = $this->validate([
            'file' => [
                'rules'  => $rules,
                'errors' => [
                    'uploaded' => 'Harus Ada File Keputusan yang diupload',
                    'mime_in'  => 'Berkas Digital Harus Berupa PDF',
                    'max_size' => $max
                ]
            ]
        ]);

        $file               = $this->request->getFile('file');
        $dir                = $this->berkas . 'usulan/tubel/' . $post['usul_id'] . '/';
        $new_name           = $post['namafile'] . '.pdf';

        if (!$input) {
            $err = $this->validator->getErrors();
            $er  = '';
            foreach ($err as $v) :
                $er .= $v . "<br>";
            endforeach;

            return redirect()->back()->with('warning', [$er]);
        } else {

            $file->move($dir, $new_name, true);

            unset($post['namafile']);
            $post['unggah_sktubel']     = 'Y';

            $status = [
                'usul_id'        => $post['usul_id'],
                'refstatus_id'   => '7', // selesai
                'usulstatus_tgl' => date('Y-m-d H:i:s'),
                'catatan'        => 'Admin Mengunggah SK Tugas Belajar, silahkan diunduh'
            ];

            @$this->tubel->updateUsulan($post);
            @$this->anjungan->addStatus($status);

            $to = base_url('dashboard/tugas-belajar/verifikasi');
            return redirect()->to($to)->with('message', ['Berhasil Mengirim Berkas']);
        }
    }
    public function batal_rekom_seleksi($id)
    {
        $id   = decry($id);
        if (!$id) return redirect()->back()->with('warning', ['Usulan belum dipilih']);

        $dir  = $this->berkas . 'usulan/tubel/' . $id . '/';
        $file = $dir . 'rekomendasi.pdf';

        //if (file_exists($file)) :
        @unlink($file);

        // update batalkan
        $update = array();
        $update['usul_id']                 = $id;
        $update['rekomendasi_seleksi']     = 'N';
        $update['rekomendasi_seleksi_tgl'] = date('Y-m-d H:i:s');

        $status = [
            'usul_id'        => $id,
            'refstatus_id'   => '11', // pembatalan urat
            'usulstatus_tgl' => date('Y-m-d H:i:s'),
            'catatan'        => 'Admin Membatalkan Rekomendasi Seleksi'
        ];

        @$this->tubel->updateUsulan($update);
        @$this->anjungan->addStatus($status);

        $to = base_url('dashboard/tugas-belajar/verifikasi');
        return redirect()->to($to)->with('message', ['Berhasil Membatalkan ']);

        //else :
        //    return redirect()->back()->with('warning', ['file tidak ditemukan']);
        //endif;
    }

    public function batal_lulus_seleksi($id)
    {
        $id   = decry($id);
        if (!$id) return redirect()->back()->with('warning', ['Usulan belum dipilih']);

        $dir  = $this->berkas . 'usulan/tubel/' . $id . '/';
        $file = $dir . 'lulus_seleksi.pdf';

        //if (file_exists($file)) :
        @unlink($file);

        // update batalkan lulus seleksi
        $update = array();
        $update['usul_id']          = $id;
        $update['unggah_lulus']     = 'N';
        $update['unggah_sktubel']   = 'N';
        $update['unggah_lulus_tgl'] = null;

        $status = [
            'usul_id'        => $id,
            'refstatus_id'   => '11', // pembatalan surat
            'usulstatus_tgl' => date('Y-m-d H:i:s'),
            'catatan'        => 'Admin Membatalkan / Menghapus File Lulus Seleksi'
        ];

        @$this->tubel->updateUsulan($update);
        @$this->anjungan->addStatus($status);

        $to = base_url('dashboard/tugas-belajar/verifikasi');
        return redirect()->to($to)->with('message', ['Berhasil Membatalkan ']);
    }
    public function update()
    {
        $post = sanitize($this->request->getPost());
        @$this->tubel->updateUsulan($post);
        return redirect()->back()->with('message', ['updated .']);
    }

    // end verifikator


    // supervisi
    public function infoSupervisi($petugas)
    {
        //dd($petugas);
        $all        = $this->tubel->allTubel();

        $baru       = $this->tubel->baruSelesaiVerifikasi(); // yang selesai diverifikasi
        $supervisi  = $this->tubel->supervisiku($petugas);

        $ms1        = carian($supervisi, 'verifikator_hasil', 'ms');
        $ms         = carian($ms1, 'supervisi_hasil', 'setuju');

        $tolak      = carian($supervisi, 'supervisi_hasil', 'tolak');
        $setuju     = carian($supervisi, 'supervisi_hasil', 'setuju');

        $tms1       = carian($supervisi, 'verifikator_hasil', 'tms');
        $tms        = carian($tms1, 'supervisi_hasil', 'setuju');

        $menunggu = carian($supervisi, 'supervisi_hasil', '-');

        //

        $info = [
            'all'           => $all,
            'jml'           => $supervisi,
            'ms'            => $ms,
            'tms'           => $tms,
            'setuju'        => $setuju,
            'tolak'         => $tolak,
            'menunggu'      => $menunggu,
            'baru'          => $baru,
        ];

        return $info;
    }

    public function supervisi($id = null)
    {
        $spid          = $this->user_ds; // id supersivi 
        $data['info']   = $this->infoSupervisi($spid);
        $res           = $this->dashboard->getKewenangan($spid);
        $data['roles'] = $res;

        if (!is_null($id)) :
            $id = decry($id);
            if (!$id) :
                return redirect()->back();
            endif;

            $cekspku = $this->tubel->supervisiCek($id, $spid);
            if (count($cekspku) == '1') :
                $asn = $this->asn->getByNip($cekspku[0]['pns_nip']);
                $cekspku[0]['pns_nipbaru']    = $asn[0]['pns_nipbaru'];
                $cekspku[0]['pns_nama']       = $asn[0]['pns_nama'];
                $cekspku[0]['pns_ketjabatan'] = $asn[0]['pns_ketjabatan'] . ' ' . $asn[0]['pns_ketopd'];
                $usulan         = $cekspku;
                $syarat         = $this->tubel->getSyarat();

                $data['usulan'] = $usulan[0];
                $data['syarat'] = $syarat;
                $data['berkas'] = $this->berkas;

                $data['detail'] = $this->tubel->getDetailTubel($usulan[0]['usul_id']);
                $data['tkpendidikan'] = $this->pg->getTkPendidikan(); // ambil dari model pg

                return view('dashboard/layanan/tubel/supervisi_open', $data);

            else :
                $cek = $this->tubel->supervisiCek($id);  // cek apakah sudah dipilih supervisor lain ?
                // apakah usulan id ini supervisinya masih null atau kosong ?                
                if (count($cek) == '1') {   // 1 artinya kosong siap dipilih              
                    $update = ['supervisi' => $spid];
                    $this->tubel->updateusulan_tbl($id, $update); // update nama supervisor              
                    return redirect()->to("dashboard/tugas-belajar/supervisi/pilih/" . encry($id));
                } else {
                    return redirect()->back()->with('warning', ['Sudah dipilih supervisor lain !']);
                }
            endif;
        else :
            $info           = $this->infoSupervisi($spid);
            $data['info']   = $info;

            $judul          = "BELUM SELESAI SUPERVISI";

            $data['result'] = $info['menunggu'];
            $data['judul']  = $judul;

            return view('dashboard/layanan/tubel/supervisi', $data);
        endif;
    }
    public function supervisi_hasil($hasil)
    {
        $spid    = $this->user_ds; // id supersivi   
        $info    = $this->infoSupervisi($spid);
        $data['info'] = $info;
        $data['hasil'] = $hasil;

        if ($hasil == 'info') :
            $judul = "SUPERVISIKU";
            $result = $info['jml'];
        elseif ($hasil == 'tolak') :
            $judul = "TOLAK DARI HASIL VERIFIKATOR";
            $result = $info['tolak'];
        elseif ($hasil == 'setuju') :
            $judul = "MENERIMA DARI HASIL VERIFIKATOR";
            $result = $info['setuju'];
        elseif ($hasil == 'ms') :
            $judul = "MEMENUHI SYARAT";
            $result = $info['ms'];
        elseif ($hasil == 'tms') :
            $judul = "TIDAK MEMENUHI SYARAT";
            $result = $info['tms'];
        elseif ($hasil == 'belum-selesai') :
            $judul = "BELUM SELESAI SUPERVISI";
            $result = $info['menunggu'];
        else :
            $judul = "BELUM DISUPERVISI";
            $result = $info['baru'];
        endif;

        $data['judul']  = $judul;
        $data['result'] = $result;

        return view('dashboard/layanan/tubel/supervisi_hasil', $data);
    }

    public function supervisi_save()
    {
        $post                  = sanitize($this->request->getPost());
        $post['supervisi_tgl'] = date('Y-m-d H:i:s');
        $id                    = $post['usul_id'];

        if ($post['supervisi_hasil'] == 'tolak' && $post['supervisi_catatan'] == '') :
            return redirect()->back()->with('warning', ['Isi catatan !']);
        endif;

        unset($post['usul_id']);

        $do = $this->tubel->do_supervisi($id, $post);

        if ($do) :
            return redirect()->to("dashboard/tugas-belajar/supervisi")->with('message', ['Berhasil disupervisi !']);
        else :
            return redirect()->back()->with('warning', ['Gagal supervisi !']);
        endif;
    }
    // end supervisi
}
