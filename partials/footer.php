<style>
    .footer {
        background-color: #ffffff; 
        padding: 60px 0 30px;
        color: #444444;
        border-top: 1px solid #eeeeee;
        font-family: 'Poppins', sans-serif;
    }

    .footer h5 {
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 25px;
        color: #1d1145;
    }

    .footer p {
        line-height: 1.8;
    }

    .footer ul li {
        margin-bottom: 12px;
    }

    .footer ul li a {
        transition: all 0.3s ease;
        display: inline-block;
    }

    .footer ul li a:hover {
        color: #e66c8a !important; 
        transform: translateX(5px);
    }

    .footer .contact-info i {
        color: #e66c8a;
        margin-right: 10px;
        font-size: 1.1rem;
    }

    .footer hr {
        margin: 40px 0 20px;
        opacity: 0.1;
    }

    .footer-bottom {
        padding-top: 10px;
    }

    .footer-bottom a:hover {
        color: #e66c8a !important;
    }

    .footer-brand span {
        color: #e66c8a;
    }

    /* CSS RESPONSIVE UNTUK MOBILE */
    @media (max-width: 768px) {
        .footer {
            padding: 40px 0 20px; 
            text-align: center; 
        }

        .footer h5 {
            margin-bottom: 15px;
            margin-top: 20px;
        }

        .footer .footer-brand {
            margin-top: 0; 
        }

        .footer p.pe-lg-5 {
            padding-right: 0 !important; 
        }

        .footer ul li a:hover {
            transform: none; 
        }

        .footer .contact-info p {
            justify-content: center;
            display: flex;
            align-items: center;
        }

        .footer hr {
            margin: 30px 0 20px;
        }

        .footer-bottom .col-md-6 {
            margin-bottom: 10px;
        }

        .footer-bottom a {
            display: inline-block;
            margin: 5px 10px; 
        }
    }
</style>

<footer id="footer" class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
                <h5 class="fw-bold footer-brand">Event<span>Ku</span></h5>
                <p class="text-muted small pe-lg-5">
                    Platform terpercaya untuk pemesanan tiket event terbaik dengan harga kompetitif dan layanan prima. Nikmati pengalaman kemudahan akses event favorit Anda hanya dalam satu genggaman.
                </p>
                <div class="mt-4">
                    <a href="#" class="me-3 text-muted fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="me-3 text-muted fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="me-3 text-muted fs-5"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>

            <div class="col-lg-4 col-6 mb-4 mb-lg-0">
                <h5 class="fw-bold">Menu Cepat</h5>
                <ul class="list-unstyled small">
                    <li><a href="index.php" class="text-decoration-none text-muted">Beranda</a></li>
                    <li><a href="events.php" class="text-decoration-none text-muted">Event Terbaru</a></li>
                    <li><a href="bookings.php" class="text-decoration-none text-muted">Pesanan Saya</a></li>
                    <li><a href="contact.php" class="text-decoration-none text-muted">Hubungi Kami</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-6 mb-4 mb-lg-0">
                <h5 class="fw-bold">Kontak Kami</h5>
                <div class="contact-info small">
                    <p class="text-muted mb-2">
                        <i class="bi bi-envelope-fill"></i> <span class="d-block d-sm-inline">info@eventku.com</span>
                    </p>
                    <p class="text-muted mb-2">
                        <i class="bi bi-telephone-fill"></i> <span class="d-block d-sm-inline">+62 123 4567 8900</span>
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-geo-alt-fill"></i> <span class="d-block d-sm-inline">Magelang, Jawa Tengah</span>
                    </p>
                </div>
            </div>
        </div>

        <hr>

        <div class="row align-items-center footer-bottom">
            <div class="col-md-6 text-muted small text-center text-md-start mb-3 mb-md-0">
                <p class="mb-0">&copy; 2026 <strong>EventKu</strong>. Semua hak dilindungi.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="text-muted text-decoration-none small mx-2 mx-md-0 ms-md-4">Kebijakan Privasi</a>
                <a href="#" class="text-muted text-decoration-none small mx-2 mx-md-0 ms-md-4">Syarat & Ketentuan</a>
                <a href="#" class="text-muted text-decoration-none small mx-2 mx-md-0 ms-md-4">FAQ</a>
            </div>
        </div>
    </div>
</footer>