<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desa Kebonsari - Beranda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="fixed-top navbar navbar-expand-lg navbar-light bg-light py-3" style="background-color: #c1cfff;">
        <div class="container-fluid">
            <img src="{{ asset('assets/sidoarjo.png') }}" style="margin-left:20px; width: 50px" alt="Logo Sidoarjo">
            <img src="{{ asset('assets/arsera-logo.png') }}" style="margin-left:20px; width: 50px" alt="Logo Arsera">

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="d-flex justify-content-center collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item me-4">
                        <a class="nav-link active" aria-current="page" href="#beranda">Beranda</a>
                    </li>
                    <li class="nav-item dropdown me-4">
                        <a class="nav-link dropdown-toggle" href="#profil" id="navbarDropdownMenuLink"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

                <!-- Authentication Links -->
                <ul class="navbar-nav ms-auto">
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

    <!-- Hero Section -->
    <div class="background-image" id="beranda">
        <img src="{{ asset('assets/hero.png') }}" alt="Hero Image"
            style="width: 100%; height: 100vh; object-fit: cover;">
        <div class="text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);">
            <h1 style="color: white; font-size: 4rem;">Selamat Datang di Website Resmi</h1>
            <h1 style="color: white; font-size: 4rem;">Desa Kebonsari</h1>
            <p style="color: white; font-size: 2rem;">Kecamatan Candi Kabupaten Sidoarjo</p>
        </div>
    </div>

    <!-- Profile Section -->
    <div class="about" id="profil" style="padding: 6rem 7%; background: #e1e1e1;">
        <div class="container">
            <h1 class="section-heading text-uppercase text-center mb-5">Profil</h1>
            <div class="profile-content justify-content-center align-items-center flex-wrap gap-4">
                <!-- Right Side - YouTube Video -->
                <div class="profile-video mb-4 smb-4 d-flex justify-content-center">
                    <iframe
                        src="https://www.youtube.com/embed/uvKZ_ytmgXQ?si=39yd5abq8YCreenz"
                        title="Mengenal Kampung Bebek Desa Kebonsari Sidoarjo"
                        frameborder="0"
                        style="width: 50%; height: 250px; border-radius: 8px;"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                </div>
                <!-- Left Side - Vision & Mission Content -->
                <div class="profile-text">
                    <div class="mb-4">
                        <h3 class="text-primary mb-3">VISI</h3>
                        <p style="text-align: justify; line-height: 1.7; color: #555;">
                            Dengan tekad perubahan terwujudnya masyarakat Desa Kebonsari yang sejahtera, maju,
                            berkarakter, berkelanjutan dengan menjunjung tinggi supremasi hukum serta norma norma
                            agama dan istiadat yang luhur
                        </p>
                    </div>

                    <div>
                        <h3 class="text-primary mb-3">MISI</h3>
                        <ol class="mission-list">
                            <li>Menciptakan Masyarakat yang berakhlak mulia dan ber-Ketuhanan Yang Maha Esa</li>
                            <li>Meningkatkan sumber daya manusia agar pintar, proffesional, berdaya guna untuk membangun dan mengolah potensi desa Kebonsari</li>
                            <li>Memberdayakan Sumber Daya Alam yang ada untuk dapat diambil manfaatnya tanpa merusak lingkungan dan tetap berwawasan lingkungan</li>
                            <li>Mewujudkan demokratis dalam segala aspek kehidupan, menghormati HAM dan supremasi Hukum</li>
                            <li>Mewujudkan kesadaran akan kebersamaan, persatuan, ketentraman, kekeluargaan, dan gotong royong agar mempunyai rasa tanggung jawab dalam bidang masing-masing serta saling hormat menghormati</li>
                            <li>Menumbuh kembangkan usaha kecil menengah</li>
                            <li>Membangun dan mendorong majunya bidang pendidikan baik formal maupun informal yang mudah diakses dan dinikmati seluruh warga masyarakat tanpa terkecuali yang mampu menghasilkan instan intelektual, inovatif dan enterpreneur (Wirausahawan)</li>
                            <li>Membangun dan mendorong usaha-usaha untuk pengembangan dan optimalisasi sektor pertanian, perkebunan, peternakan, dan perikanan, baik tahap produksi maupun tahap pengolahan hasilnya</li>
                            <li>Menciptakan Upaya Tata Kelola Pemerintahan Yang baik</li>
                            <li>Meningkatkan pembangunan infrastruktur yang mendukung perekonomian desa, serta infrastruktur strategis</li>
                        </ol>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Lembaga Desa Section -->
    <section id="lembagadesa" style="padding: 4rem 7%;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-heading text-uppercase">Lembaga Desa</h2>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="timeline-image">
                        <img class="rounded-circle img-fluid"
                            src="{{ asset('assets/lembaga-desa/tp-pkk.png') }}"
                            alt="TP PKK Desa">
                    </div>
                    <h4 class="service-heading">TP. PKK Desa</h4>
                    <p class="text-muted">Lembaga kemasyarakatan sebagai mitra kerja pemerintah dan organisasi kemasyarakatan lainnya, yang berfungsi sebagai fasilitator, perencana, pelaksana, pengendali dan penggerak pada masing-masing jenjang pemerintahan untuk terlaksananya program PKK.</p>
                </div>
                <div class="col-md-4">
                    <div class="timeline-image">
                        <img class="rounded-circle img-fluid"
                            src="{{ asset('assets/lembaga-desa/karang-taruna.png') }}"
                            alt="Karang Taruna">
                    </div>
                    <h4 class="service-heading">Karang Taruna</h4>
                    <p class="text-muted">Karang Taruna merupakan wadah pengembangan generasi muda nonpartisan, yang tumbuh atas dasar kesadaran dan rasa tanggung jawab sosial dari, oleh dan untuk masyarakat khususnya generasi muda di wilayah Desa yang bergerak dibidang kesejahteraan sosial.</p>
                </div>
                <div class="col-md-4">
                    <div class="timeline-image">
                        <img class="rounded-circle img-fluid"
                            src="{{ asset('assets/lembaga-desa/kelompok-tani.jpg') }}"
                            alt="Kelompok Tani">
                    </div>
                    <h4 class="service-heading">Kelompok Tani</h4>
                    <p class="text-muted">Kelompok tani adalah kumpulan petani/peternak/pekebun yang dibentuk atas dasar kesamaan kepentingan, kesamaan kondisi lingkungan (sosial, ekonomi, sumberdaya) dan keakraban untuk meningkatkan dan mengembangkan usaha anggota.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Berita Section -->
    <div class="our-doctors" style="padding: 4rem 7%; background-color: #e1e1e1;" id="berita">
        <h2 class="section-heading text-uppercase text-center">Berita Terkini</h2>
        <br><br>

        <div class="main-doctor" style="display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 30px;">
            <div class="inner-doctor" style="flex: 1 300px; width: 100%; position: relative;">
                <img src="{{ asset('assets/hari1.png') }}" alt="Berita 1" style="width: 100%; height: auto;">
                <a href="https://suryakabar.com/2024/10/15/menengok-kampung-bebek-dan-telur-asin-desa-kebonsari-kecamatan-candi-sidoarjo/"
                    target="_blank" rel="noopener noreferrer">Selengkapnya</a>
            </div>

            <div class="inner-doctor" style="flex: 1 300px; width: 100%; position: relative;">
                <img src="{{ asset('assets/hari2.png') }}" alt="Berita 2" style="width: 100%; height: auto;">
                <a href="https://sidoarjoterkini.com/koramil-0816-02-candi-pantau-banjir-di-desa-kebonsari-warga-diminta-waspada/"
                    target="_blank" rel="noopener noreferrer">Selengkapnya</a>
            </div>

            <div class="inner-doctor" style="flex: 1 300px; width: 100%; position: relative;">
                <img src="{{ asset('assets/hari3.png') }}" alt="Berita 3" style="width: 100%; height: auto;">
                <a href="https://kempalan.com/2025/05/02/polisi-pacu-potensi-kampung-bebek-desa-kebonsari-guna-dukung-ketahanan-pangan-nasional/"
                    target="_blank" rel="noopener noreferrer">Selengkapnya</a>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="#" class="btn btn-primary">Selengkapnya ▾</a>
        </div>
    </div>

    <!-- Maps Section -->
    <div class="why-choseus" style="padding: 4rem 7%;">
        <div class="main-chose" style="display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 30px;">
            <div class="col-lg-12 text-center" style="flex: 1 1 45rem;" id="kontak">
                <h2 class="section-heading text-uppercase">Temukan Lokasi Kami Disini</h2>
                <div class="inner-chose" style="flex: 1 1 45rem;">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31646.587508311735!2d112.68490502740818!3d-7.4847394122693025!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7e73152c73ccd%3A0x53d0333e5e69762a!2sKebonsari%2C%20Kec.%20Candi%2C%20Kabupaten%20Sidoarjo%2C%20Jawa%20Timur!5e0!3m2!1sid!2sid!4v1747642894877!5m2!1sid!2sid"
                        width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="d-flex flex-row">
        <div class="main-footer w-100" style="background-color: #e1e1e1;">
            <section id="contact">
                <div class="container">
                    <img src="{{ asset('assets/contact-bg.png') }}" style="margin-left:40px; width: 100px" alt="Contact Background">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <h2 class="section-heading text-uppercase">Kontak Kami</h2>
                            <h3 class="section-subheading text-muted">Silakan menghubungi kami melalui form di bawah ini:</h3>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <form id="contactForm" action="#" method="POST" novalidate="novalidate">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <input class="form-control"
                                                id="name" name="name" type="text" placeholder="Nama *"
                                                required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <input class="form-control"
                                                id="email" name="email" type="email" placeholder="Email *"
                                                required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <input class="form-control"
                                                id="subject" name="subject" type="text" placeholder="Perihal *"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <textarea class="form-control"
                                                id="message" name="message" rows="7" placeholder="Isi Pesan *"
                                                required></textarea>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-lg-12 text-center">
                                        <div id="success"></div>
                                        <button id="sendMessageButton" class="btn btn-primary btn-xl text-uppercase" type="submit">
                                            Kirim Pesan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Desa Kebonsari</h5>
                    <p>Kecamatan Candi, Kabupaten Sidoarjo, Jawa Timur</p>
                    <p><i class="fas fa-phone"></i> (031) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> pemdes.kebonsari.candi@gmail.com</p>
                </div>
                <div class="col-md-6">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#beranda" class="text-light">Beranda</a></li>
                        <li><a href="#profil" class="text-light">Profil</a></li>
                        <li><a href="#lembagadesa" class="text-light">Lembaga Desa</a></li>
                        <li><a href="#berita" class="text-light">Berita</a></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center flex flex-col items-center justify-between gap-6 pb-4 md:flex-row md:items-end md:gap-0">
                <img src="{{ asset('assets/unesa.png') }}" style="margin-left:20px; width: 30px; height: 30px;" alt="">
                <p>&copy; {{ date('Y') }} Sarjana Terapan Administrasi Negara Unesa. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../node_modules/flyonui/flyonui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <script>
        $(document).ready(function() {
            // Active navigation handler
            $('ul li a').click(function() {
                $('li a').removeClass("active");
                $(this).addClass("active");
            });

            // Auto-hide success message
            setTimeout(function() {
                $('#success').fadeOut('slow');
            }, 5000);

            // Smooth scrolling for anchor links
            $('a[href^="#"]').on('click', function(event) {
                var target = $(this.getAttribute('href'));
                if (target.length) {
                    event.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 70
                    }, 1000);
                }
            });

            // Contact form submission
            $('#contactForm').on('submit', function(e) {
                e.preventDefault();

                // Simple form validation
                let name = $('#name').val();
                let email = $('#email').val();
                let subject = $('#subject').val();
                let message = $('#message').val();

                if (name && email && subject && message) {
                    // Show success message
                    $('#success').html('<div class="alert alert-success">Pesan Anda telah berhasil dikirim. Terima kasih!</div>').show();

                    // Reset form
                    this.reset();

                    // Hide message after 5 seconds
                    setTimeout(function() {
                        $('#success').fadeOut('slow');
                    }, 5000);
                } else {
                    $('#success').html('<div class="alert alert-danger">Mohon lengkapi semua field yang diperlukan.</div>').show();
                }
            });
        });
    </script>

</body>

</html>
