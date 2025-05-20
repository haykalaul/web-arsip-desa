<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/resources/css/app.css">
    <link rel="stylesheet" href="fontawesome-free-5.15.3-web/css/all.min.css">
    <link
        rel="stylesheet"
        href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>

    <nav class="  fixed-top navbar navbar-expand-lg navbar-light bg-light py-3" style="background-color: #c1cfff;;">
        <div class="container-fluid">
            <img src="{{ asset('assets/sidoarjo.png') }}" style="margin-left:20px; width: 50px" alt="">
            <img src="images/kemenkes1.png" style="margin-left:40px; width: 100px" alt="">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="  d-flex justify-content-center collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item me-4">
                        <a class="nav-link active" aria-current="page" href="#">Beranda</a>
                    </li>
                    <li class="nav-item dropdown me-4">
                        <a class="nav-link dropdown-toggle" href="#profil" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Profile
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" href="#profil">Visi & Misi</a></li>
                            <li><a class="dropdown-item" href="#profil">Struktur Organisasi</a></li>
                            <li><a class="dropdown-item" href="#profil">Daftar Pegawai</a></li>
                            <li><a class="dropdown-item" href="#profil">Profil Pimpinan</a></li>
                            <li><a class="dropdown-item" href="#profil">Tugas & Fungsi</a></li>
                        </ul>
                    </li>
                    </li>
                    <li class="nav-item me-4">
                        <a class="nav-link" href="#lembagadesa">Lembaga Desa</a>
                    </li>
                    <li class="nav-item me-4">
                        <a class="nav-link" href="#berita">Berita</a>
                    </li>
                    <li class="nav-item me-4">
                        <a class="nav-link" href="#kontak">Kontak</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto"> {{-- ms-auto untuk dorong ke kanan --}}
                    @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="btn btn-outline-primary ms-3" href="{{ route('login') }}">Login</a>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="background-image" id="beranda">
        <img src="{{ asset('assets/herosection.png') }}" alt="">
    </div>

    <div class="about" id="profil" style="padding: 6rem 7%;background:#e1e1e1;">
        <div class="main-about" style="display: flex;justify-content:center;align-items:center;gap:15px;flex-wrap:wrap;">
            <div class="inner-about" style="flex:1 1 45rem;">
                <br class="about-content" style="width: 80%;">
                <h1>Profil</h1><br>
                <br>VISI<br>Dengan tekad perubahan terwujudnya masyarakat Desa Kebonsari yang sejahtera,maju,berkarakter,berkelanjutan dengan menjunjung tinggi supremasi hukum serta norma norma agama dan istiadat yang luhur</br><br>MISI<br>
                <ol>
                    <li>Menciptakan Masyarakat yang berakhlak mulia dan ber-Ketuhanan Yang Maha Esa</li>
                    <li>Meningkatkan sumber daya manusia agar pintar, proffesional, berdaya guna untuk membangun dan mengolah potensi desa Kebonsari</li>
                    <li>Memberdayakan Sumber Daya Alam yang ada untuk dapat diambil manfaatnya tanpa merusak lingkungan dan tetap berwawasan lingkungan</li>
                    <li>Mewujudkan demokratis dalam segala aspek kehidupan,menghormati HAM dan supremasi Hukum</li>
                    <li>Mewujudkan kesadaran akan kebersamaan, persatuan, ketentraman, kekeluargaan, dan gotong royong agar mempunyai rasa tanggung jawab dalam bidang masing-asing serta saling hormat menghormati</li>
                    <li>Menumbuh kembangkan usaha kecil menengah</li>
                    <li>Membangun dan mendorong majunya bidang pendidikan baik formal maupun informal yang mudah diakses dan dinikmati seluruh warga masyarakat tanpa terkecuali yang mampu menghasilkan isntan intelektual, inovatif dan enterpreneur (Wirausahawan)</li>
                    <li>Membangun dan mendorong usaha-usaha untuk pengembangan dan optimalisasi sektor pertanian, perkebunan, peternakan, dan perikanan, baik tahap produksi maupun tahap pengolahan hasilnya</li>
                    <li>Menciptakan Upaya Tata Kelola Pemerintahan Yang baik</li>
                    <li>Meningkatkan pembangunan infrastruktur yang mendukung perekonomian desa,serta infrastruktur strategis</li>
                    </p>
                    <a style="background: #e1e1e1; padding: 1rem 3rem;font-size: 1.5rem;color:black;border-radius: 10px;transition: .5s;" href="#">Selengkapnya ▾</a>
            </div>

        </div>
        <div class="inner-about" style="flex:1 1 45rem;">
            <div class="inner-about-image" style="width: 100%;">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/uvKZ_ytmgXQ?si=39yd5abq8YCreenz" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    </div>

    <!-- Lembaga Desa -->
    <section id="lembagadesa">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-heading text-uppercase">Lembaga Desa</h2>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="timeline-image">
                        <img class="rounded-circle img-fluid" src="{{ asset('assets/lembaga-desa/tp-pkk.png') }}" alt="">
                    </div>
                    <h4 class="service-heading">TP. PKK Desa</h4>
                    <p class="text-muted">Lembaga kemasyarakatan sebagai mitra kerja pemerintah dan organisasi kemasyarakatan lainnya, yang berfungsi sebagai fasilitator, perencana, pelaksana, pengendali dan penggerak pada masing-masing jenjang pemerintahan untuk terlaksananya program PKK.</p>
                </div>
                <div class="col-md-4">
                    <div class="timeline-image">
                        <img class="rounded-circle img-fluid" src="{{ asset('assets/lembaga-desa/karang-taruna.png') }}" alt="">
                    </div>
                    <h4 class="service-heading">Karang Taruna</h4>
                    <p class="text-muted">Karang Taruna merupakan wadah pengembangan generasi muda nonpartisan, yang tumbuh atas dasar kesadaran dan rasa tanggung jawab sosial dari, oleh dan untuk masyarakat khususnya generasi muda di wilayah Desa yang bergerak dibidang kesejahteraan sosial.</p>
                </div>
                <div class="col-md-4">
                    <div class="timeline-image">
                        <img class="rounded-circle img-fluid" src="{{ asset('assets/lembaga-desa/kelompok-tani.jpg') }}" alt="">
                    </div>
                    <h4 class="service-heading">Kelompok Tani</h4>
                    <p class="text-muted">Kelompok tani adalah kumpulan petani/peternak/pekebun yang dibentuk atas dasar kesamaan kepentingan, kesamaan kondisi lingkungan (sosial, ekonomi, sumberdaya) dan keakraban untuk meningkatkan dan mengembangkan usaha anggota.</p>
                </div>
            </div>
        </div>
    </section>


    <div class="our-doctors" style="padding: 4rem 7%; background-color: #e1e1e1;" id="berita">
        <h2 class="section-heading text-uppercase text-center">Berita Terkini</h2>
        <br><br>

        <div class="main-doctor" style="display:flex;justify-content: center;align-items: center;flex-wrap: wrap;gap: 30px;">
            <div class="inner-doctor" style="flex: 1 300px;position: relative;">
                <img src="{{ asset('assets/hari1.jpg') }}" a href="https://suryakabar.com/2024/10/15/menengok-kampung-bebek-dan-telur-asin-desa-kebonsari-kecamatan-candi-sidoarjo/" alt="">
            </div>

            <div class="inner-doctor" style="flex: 1 300px;position: relative;">
                <img src="{{ asset('assets/hari2.jpg') }}" a href="https://sidoarjoterkini.com/koramil-0816-02-candi-pantau-banjir-di-desa-kebonsari-warga-diminta-waspada/" alt="">
            </div>

            <div class="inner-doctor" style="flex: 1 300px;position: relative;">
                <img src="{{ asset('assets/hari3.jpg') }}" a href="https://kempalan.com/2025/05/02/polisi-pacu-potensi-kampung-bebek-desa-kebonsari-guna-dukung-ketahanan-pangan-nasional/" alt="">
            </div>
            <a href="#">Selengkapnya ▾</a>
        </div>
    </div>

    <div class="why-choseus" style="padding: 4rem 7%;">
        <div class="main-chose" style="display: flex;justify-content: center;align-items: center;flex-wrap: wrap;gap: 30px;">
            <div class="col-lg-12 text-center" style="flex: 1 1 45rem;" id="kontak">
                <h2 class="section-heading text-uppercase">Temukan Lokasi Kami Disini</h2>
                <div class="inner-chose" style="flex: 1 1 45rem;">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31646.587508311735!2d112.68490502740818!3d-7.4847394122693025!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7e73152c73ccd%3A0x53d0333e5e69762a!2sKebonsari%2C%20Kec.%20Candi%2C%20Kabupaten%20Sidoarjo%2C%20Jawa%20Timur!5e0!3m2!1sid!2sid!4v1747642894877!5m2!1sid!2sid" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>



    <div class="d-flex flex-row footer ">
        <div class="main-footer  w-100" style="background-color: #e1e1e1;">

            <!-- Kontak -->
            <section id="contact">
                <div class="container">
                    <img src="{{ asset('assets/contact-bg.png') }}" style="margin-left:40px; width: 100px" alt="">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <h2 class="section-heading text-uppercase">Kontak Kami</h2>
                            <h3 class="section-subheading text-muted">Silakan menghubungi kami melalui form di bawah ini:</h3>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <form id="contactForm" name="sentMessage" novalidate="novalidate">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input class="form-control" id="name" type="text" placeholder="Nama *" required="required" data-validation-required-message="Silahkan masukkan nama Anda terlebih dahulu.">
                                            <p class="help-block text-danger"></p>
                                        </div>
                                        <div class="form-group">
                                            <input class="form-control" id="email" type="email" placeholder="Email *" required="required" data-validation-required-message="Silahkan masukkan email Anda terlebih dahulu.">
                                            <p class="help-block text-danger"></p>
                                        </div>
                                        <div class="form-group">
                                            <input class="form-control" id="subject" type="tel" placeholder="Perihal *" required="required" data-validation-required-message="Silahkan masukkan perihal terlebih dahulu.">
                                            <p class="help-block text-danger"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <textarea class="form-control" id="message" placeholder="Isi Pesan *" required="required" data-validation-required-message="Silahkan isi pesan terlebih dahulu."></textarea>
                                            <p class="help-block text-danger"></p>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-lg-12 text-center">
                                        <div id="success"></div>
                                        <button id="sendMessageButton" class="btn btn-primary btn-xl text-uppercase" type="submit">Kirim Pesan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            @include('components.footer')
            <!-- / Footer -->

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"> </script>
    <script>
        $(document).ready(function() {
            $('ul li a').click(function() {
                $('li a').removeClass("active");
                $(this).addClass("active");
            });
        });
    </script>
</body>

</html>


<!-- provide an exceptional patient experience
<i class="fas fa-notes-medical"></i>
<i class="fas fa-hospital-user"></i>
<i class="fas fa-user-md"></i>
