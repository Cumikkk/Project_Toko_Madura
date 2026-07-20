<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liveness Check 3 Step</title>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <style>
        body { font-family: sans-serif; text-align: center; background: #f4f7f6; margin-top: 30px; }
        #kamera-container { position: relative; display: inline-block; margin-top: 10px; }
        video, canvas { transform: scaleX(-1); border-radius: 8px; border: 2px solid #ccc; } 
        canvas { position: absolute; top: 0; left: 0; }
        
        .checklist-box { margin-top: 20px; padding: 15px; font-size: 18px; font-weight: bold; background: #fff; border-radius: 8px; display: inline-block; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: left; }
        .checklist-item { margin: 10px 0; display: flex; align-items: center; }
        
        /* Warna Status */
        .status-merah { color: #d9534f; }
        .status-hijau { color: #28a745; }
        .icon { margin-right: 10px; font-size: 24px; }
    </style>
</head>
<body>

    <h2>Verifikasi Wajah</h2>
    <div id="kamera-container">
        <video id="kamera" width="400" height="300" autoplay muted playsinline></video>
        <canvas id="overlay"></canvas>
    </div>

    <br>
    <div class="checklist-box">
        <div id="task-1" class="checklist-item status-merah">
            <span class="icon">⭕</span> 1. Kedipkan Mata
        </div>
        <div id="task-2" class="checklist-item status-merah">
            <span class="icon">⭕</span> 2. Gelengkan Kepala
        </div>
        <div id="task-3" class="checklist-item status-merah">
            <span class="icon">⭕</span> 3. Buka Mulut
        </div>
    </div>

    <script>
        const video = document.getElementById('kamera');
        const canvas = document.getElementById('overlay');
        
        // Element Checklist
        const elTask1 = document.getElementById('task-1');
        const elTask2 = document.getElementById('task-2');
        const elTask3 = document.getElementById('task-3');

        // Pastikan arahkan ke folder models lokal sesuai setup Nginx kamu
        const MODEL_URL = './models';

        // Variabel State (Langkah yang sedang aktif)
        let stepSaatIni = 1; 

        // Variabel pembantu untuk deteksi
        let isMataTertutup = false;
        let arahGeleng = 0; // 0: tengah, 1: kiri, 2: kanan

        async function initLiveness() {
            // Pengecekan HTTPS / Localhost
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert("Kamera diblokir! Pastikan akses lewat localhost atau HTTPS.");
                return;
            }

            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                    .then(stream => { video.srcObject = stream; })
                    .catch(err => { alert("Akses kamera ditolak / tidak ditemukan."); });
            } catch (error) {
                alert("Gagal memuat model. Cek file di folder ./models");
                console.error(error);
            }
        }

        // --- RUMUS MATEMATIKA UNTUK WAJAH ---
        function hitungJarak(p1, p2) {
            return Math.sqrt(Math.pow(p1.x - p2.x, 2) + Math.pow(p1.y - p2.y, 2));
        }

        function hitungEAR(mata) { // Eye Aspect Ratio
            return (hitungJarak(mata[1], mata[5]) + hitungJarak(mata[2], mata[4])) / (2.0 * hitungJarak(mata[0], mata[3]));
        }

        function hitungMAR(mulut) { // Mouth Aspect Ratio
            // Indeks 14 dan 18 adalah bibir bagian dalam atas & bawah
            // Indeks 0 dan 6 adalah ujung sudut bibir kiri & kanan
            return hitungJarak(mulut[14], mulut[18]) / hitungJarak(mulut[0], mulut[6]);
        }

        function hitungRasioHidung(rahang, hidung) {
            // Menghitung posisi hidung terhadap lebar rahang
            const lebarWajah = rahang[16].x - rahang[0].x;
            const jarakHidungKeKiri = hidung[3].x - rahang[0].x;
            return jarakHidungKeKiri / lebarWajah;
        }

        // --- PROSES DETEKSI UTAMA ---
        video.addEventListener('play', () => {
            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);

            const deteksiInterval = setInterval(async () => {
                // Kalau step sudah lewat 3, hentikan proses AI
                if (stepSaatIni > 3) {
                    clearInterval(deteksiInterval);
                    return;
                }

                const deteksi = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();
                const context = canvas.getContext('2d');
                context.clearRect(0, 0, canvas.width, canvas.height);

                if (deteksi) {
                    const landmarks = deteksi.landmarks;
                    // Gambar wireframe ke wajah biar kelihatan canggih
                    faceapi.draw.drawFaceLandmarks(canvas, faceapi.resizeResults(deteksi, displaySize));

                    // --- STEP 1: KEDIPKAN MATA ---
                    if (stepSaatIni === 1) {
                        const earKiri = hitungEAR(landmarks.getLeftEye());
                        const earKanan = hitungEAR(landmarks.getRightEye());
                        const avgEAR = (earKiri + earKanan) / 2;

                        if (avgEAR < 0.25) {
                            isMataTertutup = true;
                        } else if (isMataTertutup && avgEAR >= 0.25) {
                            // Mata terbuka kembali -> Kedip sukses!
                            elTask1.classList.replace('status-merah', 'status-hijau');
                            elTask1.innerHTML = '<span class="icon">✅</span> 1. Kedipkan Mata';
                            stepSaatIni = 2; // Lanjut ke step 2
                        }
                    }

                    // --- STEP 2: GELENG KEPALA ---
                    if (stepSaatIni === 2) {
                        const rasioHidung = hitungRasioHidung(landmarks.getJawOutline(), landmarks.getNose());
                        
                        // Normalnya menatap lurus itu rasio ~0.5
                        // Kalau nengok kiri rasio membesar, nengok kanan rasio mengecil (karena efek cermin / miror camera)
                        if (rasioHidung < 0.35) {
                            arahGeleng = 1; // Sudah nengok satu arah
                        } else if (rasioHidung > 0.65) {
                            if (arahGeleng === 1) {
                                // Sudah nengok kebalikannya -> Geleng sukses!
                                elTask2.classList.replace('status-merah', 'status-hijau');
                                elTask2.innerHTML = '<span class="icon">✅</span> 2. Gelengkan Kepala';
                                stepSaatIni = 3; // Lanjut step 3
                            } else {
                                arahGeleng = 2;
                            }
                        } else if (rasioHidung < 0.35 && arahGeleng === 2) {
                             // Kombinasi arah sebaliknya
                             elTask2.classList.replace('status-merah', 'status-hijau');
                             elTask2.innerHTML = '<span class="icon">✅</span> 2. Gelengkan Kepala';
                             stepSaatIni = 3;
                        }
                    }

                    // --- STEP 3: BUKA MULUT ---
                    if (stepSaatIni === 3) {
                        const rasioMulut = hitungMAR(landmarks.getMouth());
                        
                        // Kalau rasio bukaan mulut lebih dari 0.4, dianggap mangap
                        if (rasioMulut > 0.4) {
                            elTask3.classList.replace('status-merah', 'status-hijau');
                            elTask3.innerHTML = '<span class="icon">✅</span> 3. Buka Mulut';
                            stepSaatIni = 4; // Semua selesai
                            
                            // Bersihkan canvas
                            context.clearRect(0, 0, canvas.width, canvas.height); 
                            
                            // Eksekusi fungsi akhir
                            selesaiLiveness();
                        }
                    }
                }
            }, 100); // Looping tiap 100ms
        });

        function selesaiLiveness() {
            setTimeout(() => {
                alert("Semua verifikasi liveness berhasil!");
                
                // --- Ambil foto Base64 untuk dikirim ke PHP ---
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = video.width;
                tempCanvas.height = video.height;
                tempCanvas.getContext('2d').drawImage(video, 0, 0, tempCanvas.width, tempCanvas.height);
                const base64Image = tempCanvas.toDataURL('image/jpeg', 0.9);

                // Silakan panggil fetch() di sini untuk mengirim base64Image ke proses_liveness.php
                console.log("Siap kirim ke server!");
                
            }, 500);
        }

        // Jalankan saat load
        window.onload = initLiveness;
    </script>
</body>
</html>