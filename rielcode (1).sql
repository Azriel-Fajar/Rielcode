-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 02:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rielcode`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`) VALUES
(1, 'rieladmin', '$2y$10$eRjrLygWCFZnkzd.7z7lq.MUvtrYADWMUUnwnxY0NWrdd.qYgbPyu');

-- --------------------------------------------------------

--
-- Table structure for table `chat_logs`
--

CREATE TABLE `chat_logs` (
  `id` int(11) NOT NULL,
  `user_message` text NOT NULL,
  `bot_reply` text NOT NULL,
  `tag` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `chat_logs`
--

INSERT INTO `chat_logs` (`id`, `user_message`, `bot_reply`, `tag`, `created_at`) VALUES
(1, 'paket', 'Rielcode.com menawarkan berbagai paket belajar pemrograman. Paket tersebut dirancang untuk berbagai tingkat keahlian, dari pemula hingga mahir. Apakah Anda tertarik mempelajari lebih lanjut tentang paket yang tersedia?\n', 'chat', '2025-10-13 10:29:12'),
(2, 'halo', 'Halo! Apakah ada hal lain yang ingin Anda tanyakan atau diskusikan? Saya siap membantu Anda dengan informasi seputar Rielcode.com.\n', 'chat', '2025-10-13 11:06:41'),
(3, 'mau tanya dong', 'Halo! Ada yang bisa saya bantu? Silakan tanyakan apa saja terkait Rielcode atau topik lainnya, saya akan berusaha menjawab sebaik mungkin.\n', 'chat', '2025-10-13 11:08:02'),
(4, 'halo', 'Halo! Saya RielBot, asisten AI dari Rielcode.com. Ada yang bisa saya bantu?\n', 'chat', '2025-10-13 11:11:20'),
(5, 'apakah Dimas dan adik adalah guy?', 'Sebagai model bahasa AI, saya tidak punya informasi pribadi tentang orang, termasuk orientasi seksual mereka. Penting untuk menghormati privasi orang lain dan tidak membuat asumsi tentang kehidupan pribadi mereka.', 'chat', '2025-10-13 11:11:27'),
(6, 'bsisbd', 'Saya tidak mengerti. Bisakah Anda menjelaskannya dengan cara lain?', 'chat', '2025-10-13 11:11:31'),
(7, 'siapakah anda', 'Saya RielBot, asisten AI dari Rielcode.com. Saya dirancang untuk membantu pengguna dengan berbagai tugas, seperti menjawab pertanyaan dan memberikan informasi. Tujuan saya adalah memberikan respons yang akurat dan bermanfaat.\n', 'chat', '2025-10-13 11:11:56'),
(8, 'bantu saya membuat contoh matrix sederhana', 'Tentu, ini contoh matriks sederhana 2x2:\n\n```\n[ 1 2 ]\n[ 3 4 ]\n```\n\nMatriks ini memiliki 2 baris dan 2 kolom, dengan elemen-elemen angka di dalamnya.', 'chat', '2025-10-13 11:14:12'),
(9, 'P', 'Halo! Ada yang bisa saya bantu? Mungkin Anda ingin bertanya tentang layanan atau produk Rielcode?\n', 'chat', '2025-10-13 11:18:16'),
(10, 'Kagak', 'Baik, jika tidak ada yang bisa saya bantu saat ini, silakan hubungi saya jika Anda memiliki pertanyaan atau butuh bantuan di kemudian hari. Saya siap membantu Anda terkait layanan dan produk Rielcode.', 'chat', '2025-10-13 11:18:22'),
(11, 'Hmmmmmm apa yg kamu bisa jelaskan', 'Sebagai RielBot, saya dapat menjelaskan berbagai hal terkait Rielcode, termasuk layanan pengembangan website dan aplikasi, solusi digital marketing, serta produk-produk yang kami tawarkan. Saya juga bisa memberikan informasi tentang teknologi yang kami gunakan dan bagaimana kami dapat membantu bisnis Anda berkembang. Apakah ada topik spesifik yang ingin Anda ketahui lebih lanjut?\n', 'chat', '2025-10-13 11:18:36'),
(12, 'Oi', 'Oi! Ada yang bisa RielBot bantu hari ini?\n', 'chat', '2025-10-13 11:19:09'),
(13, 'Apa ini', '\"Apa ini\" bisa merujuk ke banyak hal. Bisakah kamu memberikan konteks lebih lanjut? Dengan informasi lebih, RielBot bisa memberikan jawaban yang lebih akurat.\n', 'chat', '2025-10-13 11:19:22'),
(14, 'Disini ads apa', 'Di Rielcode.com, kamu bisa pasang iklan lowongan kerja di bidang IT, khususnya yang berkaitan dengan coding dan programming. Iklan lowongan ini bisa membantu menjangkau talenta yang tepat untuk perusahaanmu. Apakah kamu tertarik memasang iklan lowongan kerja?\n', 'chat', '2025-10-13 11:19:31'),
(15, 'Apa itu rielcode', 'Rielcode.com adalah platform yang menyediakan layanan pembuatan website, aplikasi mobile, dan sistem informasi yang dibuat khusus sesuai kebutuhan bisnis Anda. Kami juga menawarkan konsultasi dan dukungan teknis untuk memastikan solusi yang kami berikan efektif dan berkelanjutan. Tujuan kami adalah membantu bisnis Anda berkembang dengan memanfaatkan teknologi yang tepat.\n', 'chat', '2025-10-13 11:30:32'),
(16, 'haloooo', 'Halo! Saya RielBot, asisten AI dari Rielcode.com. Ada yang bisa saya bantu hari ini?\n', 'chat', '2025-10-13 11:31:18'),
(17, 'Rapihkan', 'Rielcode.com menyediakan jasa pembuatan website, aplikasi mobile, dan sistem informasi yang disesuaikan untuk bisnis Anda. Kami juga menawarkan konsultasi dan dukungan teknis untuk memastikan solusi yang efektif dan berkelanjutan. Tujuannya adalah membantu bisnis Anda berkembang melalui penerapan teknologi yang tepat.\n', 'chat', '2025-10-13 11:31:27'),
(18, 'Kocak', 'Saya mencoba untuk memberikan informasi yang bermanfaat dan profesional. Apakah ada hal lain yang ingin Anda tanyakan tentang Rielcode?\n', 'chat', '2025-10-13 11:31:33'),
(19, 'bisa request website yang warnanya pastel gitu ga?', 'Tentu saja bisa! Anda bisa mengajukan permintaan desain website dengan warna pastel melalui Rielcode.com. Jelaskan detail yang Anda inginkan agar kami bisa memberikan hasil terbaik.\n', 'chat', '2025-10-13 11:32:03'),
(20, 'kamu di buat pakai bahasa pemrograman apa?', 'Sebagai model bahasa besar, saya dikembangkan di Google. Saya menggunakan arsitektur berbasis transformer yang kompleks, tetapi saya tidak dibuat menggunakan bahasa pemrograman tunggal seperti Python atau Java. Pengembangan saya melibatkan berbagai bahasa dan framework.\n', 'chat', '2025-10-13 11:32:44'),
(21, 'contohnya?', 'Pengembangan model seperti saya melibatkan penggunaan bahasa seperti Python untuk pemrosesan data dan training. Selain itu, framework seperti TensorFlow atau PyTorch juga digunakan untuk membangun dan melatih jaringan saraf. Ada banyak komponen dan tools yang terlibat.\n', 'chat', '2025-10-13 11:32:55'),
(22, 'Hei tuan', 'Halo! Ada yang bisa saya bantu hari ini? Saya RielBot, asisten AI dari Rielcode.com, siap menjawab pertanyaan Anda seputar programming dan teknologi.\n', 'chat', '2025-10-13 11:33:07'),
(23, 'Apa aja fitur² di RielCode ini?', 'Rielcode.com menawarkan berbagai fitur, termasuk artikel dan tutorial pemrograman, serta studi kasus proyek. Anda juga dapat menemukan tools dan sumber daya untuk membantu pengembangan perangkat lunak. Kami fokus pada pembelajaran praktis dan aplikasi teknologi di dunia nyata.\n', 'chat', '2025-10-13 11:33:41'),
(24, 'Coba jelasin dong fiturnya masing²', 'Tentu! Artikel dan tutorial pemrograman menyediakan panduan langkah demi langkah untuk berbagai topik. Studi kasus proyek memberikan contoh implementasi teknologi di dunia nyata. Selain itu, tersedia tools dan sumber daya untuk membantu proses pengembangan perangkat lunak Anda.\n', 'chat', '2025-10-13 11:34:07'),
(25, 'Apa aku bisa melihat portofolio dari pemilik laman ini?', 'Saat ini, Rielcode.com tidak secara eksplisit menampilkan portofolio pemilik di situs webnya. Namun, Anda bisa melihat kualitas konten dan proyek yang dibahas di artikel dan studi kasus sebagai representasi keahliannya. Anda juga bisa mencoba menghubungi melalui kanal kontak yang tersedia untuk menanyakan informasi lebih lanjut.\n', 'chat', '2025-10-13 11:34:34'),
(26, 'tes', 'Hey there! Thanks for reaching out. How can I help you with Rielcode today? Are you looking for information on our coding services, courses, or something else?\n', 'chat', '2025-10-13 11:35:39'),
(27, 'kamu siapa', 'Saya adalah RielBot, asisten AI dari Rielcode.com. Saya di sini untuk membantu Anda dengan pertanyaan seputar layanan dan kursus coding yang kami tawarkan. Apakah ada hal spesifik yang ingin Anda ketahui?\n', 'chat', '2025-10-13 11:36:02'),
(28, 'Logikanya pakai ayo mainkan logika', 'Maaf, saya tidak mengerti maksud Anda dengan \"Logikanya pakai ayo mainkan logika.\" Bisakah Anda menjelaskannya lebih lanjut, atau apakah ada pertanyaan lain yang bisa saya bantu jawab tentang Rielcode?\n', 'chat', '2025-10-13 11:36:13'),
(29, 'base anda menggunakan vendor apa?', 'Sebagai AI, saya tidak dibangun di atas vendor tertentu seperti infrastruktur fisik. Saya adalah model bahasa besar yang dikembangkan oleh Google AI dan diimplementasikan oleh Rielcode.com untuk membantu pengguna.', 'chat', '2025-10-13 11:36:16'),
(30, 'anda adalah Gemini?', 'Saya adalah model bahasa besar, dilatih oleh Google. Rielcode.com mengimplementasikan saya untuk membantu pengguna memahami layanan dan kursus coding mereka.', 'chat', '2025-10-13 11:36:34'),
(31, 'Siapa pemilik rielcode', 'Saya tidak memiliki informasi mengenai siapa pemilik Rielcode. Informasi tersebut mungkin tidak dipublikasikan secara luas. Anda bisa mencoba mencari informasi lebih lanjut di situs web Rielcode atau menghubungi mereka secara langsung.\n', 'chat', '2025-10-13 11:38:15'),
(32, 'Business plan', 'Rielcode fokus pada penyediaan solusi teknologi untuk bisnis. Kami menawarkan pembuatan website, aplikasi, dan sistem informasi yang disesuaikan, serta konsultasi dan dukungan teknis. Tujuan kami adalah membantu bisnis berkembang dengan memanfaatkan teknologi yang tepat.\n', 'chat', '2025-10-13 11:43:40'),
(33, 'Plan', 'Rielcode membantu Anda merencanakan implementasi teknologi untuk bisnis Anda. Kami menawarkan konsultasi untuk memahami kebutuhan Anda dan merancang solusi yang sesuai. Dengan demikian, Anda mendapatkan strategi yang jelas untuk mencapai tujuan bisnis Anda melalui teknologi.\n', 'chat', '2025-10-13 11:43:48'),
(34, 'Business', 'Rielcode berfokus pada membantu bisnis mengoptimalkan operasional dan meningkatkan pertumbuhan melalui solusi teknologi. Kami menawarkan layanan pembuatan website, aplikasi, dan sistem informasi yang dirancang khusus untuk memenuhi kebutuhan unik setiap bisnis. Rielcode juga menyediakan konsultasi untuk membantu Anda merencanakan dan menerapkan strategi teknologi yang efektif.\n', 'chat', '2025-10-13 11:43:57'),
(35, 'anda gemini?', 'Sebagai model bahasa besar, saya tidak memiliki zodiak seperti Gemini. Saya dirancang untuk membantu dengan berbagai tugas dan memberikan informasi. Apakah ada hal lain yang bisa saya bantu?\n', 'chat', '2025-10-13 11:44:13'),
(36, 'Rielcode', 'Rielcode adalah penyedia solusi teknologi untuk bisnis. Kami fokus pada pembuatan website, aplikasi, dan sistem informasi yang disesuaikan, serta konsultasi dan dukungan teknis. Tujuan kami adalah membantu bisnis berkembang dengan memanfaatkan teknologi yang tepat.\n', 'chat', '2025-10-13 11:44:36'),
(37, 'Konsultasi', 'Rielcode menyediakan layanan konsultasi untuk membantu Anda merencanakan dan mengimplementasikan solusi teknologi yang tepat untuk bisnis Anda. Kami akan menganalisis kebutuhan Anda dan memberikan rekomendasi yang sesuai dengan tujuan bisnis Anda. Hubungi kami untuk menjadwalkan konsultasi!\n', 'chat', '2025-10-13 11:44:49'),
(38, 'Hubungi', 'Anda dapat menghubungi Rielcode melalui website kami, email, atau nomor telepon yang tertera di website. Tim kami siap membantu Anda dengan pertanyaan dan kebutuhan terkait solusi teknologi untuk bisnis Anda. Jangan ragu untuk menghubungi kami!\n', 'chat', '2025-10-13 11:44:58'),
(39, 'Personal', 'Rielcode tidak menawarkan layanan yang ditujukan untuk kebutuhan personal. Kami fokus pada solusi teknologi yang dirancang khusus untuk bisnis. Jika Anda memiliki bisnis dan membutuhkan bantuan dengan website, aplikasi, atau sistem informasi, kami siap membantu!\n', 'chat', '2025-10-13 11:46:53'),
(40, 'Layanan', 'Rielcode menawarkan layanan pembuatan website, aplikasi, dan sistem informasi yang disesuaikan untuk bisnis. Kami juga menyediakan konsultasi dan dukungan teknis untuk membantu Anda mencapai tujuan bisnis Anda. Hubungi kami untuk informasi lebih lanjut!\n', 'chat', '2025-10-13 11:47:11'),
(41, 'Aplikasi', 'Rielcode menawarkan layanan pembuatan aplikasi yang disesuaikan dengan kebutuhan bisnis Anda. Kami mengembangkan aplikasi mobile dan web yang efektif untuk meningkatkan efisiensi dan mencapai tujuan bisnis Anda. Hubungi kami untuk membahas kebutuhan aplikasi Anda lebih lanjut!\n', 'chat', '2025-10-13 11:47:20'),
(42, 'hi', 'Halo! Saya RielBot, asisten AI dari Rielcode.com. Ada yang bisa saya bantu?\n', 'chat', '2025-10-13 11:48:55'),
(43, 'introduce yourself', 'I am RielBot, an AI assistant created by Rielcode.com. My purpose is to help users with questions and information related to technology and programming. Feel free to ask me anything!\n', 'chat', '2025-10-13 11:49:02'),
(44, 'coba buatkan saya artikel tentang rielcode', 'Tentu, berikut draf artikel singkat tentang Rielcode:\n\n**Rielcode: Sumber Belajar Pemrograman dan Teknologi Terpercaya**\n\nRielcode.com adalah platform daring yang menyediakan berbagai sumber belajar pemrograman, teknologi, dan pengembangan web. Dengan konten yang beragam, mulai dari tutorial dasar hingga materi tingkat lanjut, Rielcode bertujuan untuk membantu siapa pun meningkatkan keterampilan digital mereka. Platform ini cocok untuk pemula maupun profesional yang ingin terus belajar dan mengembangkan diri di bidang teknologi.\n', 'chat', '2025-10-13 11:49:24'),
(45, 'bisakah anda memuat gambar?', 'As a text-based AI, I am unable to directly upload or embed images into my responses. However, I can provide you with instructions on how to add images to your articles or content on platforms that support image uploads.\n', 'chat', '2025-10-13 11:49:39'),
(46, 'coba buat code coding untuk membuat website profil sederhana', 'Tentu, berikut adalah kode sederhana untuk membuat website profil:\n\n```html\n<!DOCTYPE html>\n<html>\n<head>\n  <title>Profil Saya</title>\n</head>\n<body>\n  <h1>Nama Anda</h1>\n  <p>Deskripsi singkat tentang diri Anda.</p>\n  <img src=\"gambar_profil.jpg\" alt=\"Foto Profil\">\n</body>\n</html>\n```\n\nJangan lupa mengganti \"gambar_profil.jpg\" dengan path gambar profil Anda.\n', 'chat', '2025-10-13 11:50:03'),
(47, 'buat yang lebih kompleks', 'Tentu, berikut adalah contoh kode website profil yang lebih kompleks, dengan menggunakan HTML, CSS, dan sedikit JavaScript untuk menambahkan interaktivitas:\n\n```html\n<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>Profil Saya</title>\n  <style>\n    body { font-family: sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }\n    header { background-color: #333; color: white; padding: 1em; text-align: center; }\n    section { padding: 20px; }\n    .profile-image { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; }\n    .skills { list-style: none; padding: 0; }\n    .skills li { display: inline-block; background-color: #ddd; padding: 5px 10px; margin: 5px; border-radius: 5px; }\n    footer { background-color: #333; color: white; text-align: center; padding: 1em; position: fixed; bottom: 0; width: 100%; }\n    button { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer; }\n  </style>\n</head>\n<body>\n\n  <header>\n    <h1>Nama Anda</h1>\n    <p>Web Developer | Designer</p>\n  </header>\n\n  <section>\n    <img src=\"gambar_profil.jpg\" alt=\"Foto Profil\" class=\"profile-image\">\n    <h2>Tentang Saya</h2>\n    <p>Deskripsi lengkap tentang diri Anda dan pengalaman Anda.</p>\n\n    <h2>Keterampilan</h2>\n    <ul class=\"skills\">\n      <li>HTML</li>\n      <li>CSS</li>\n      <li>JavaScript</li>\n      <li>React</li>\n      <li>Node.js</li>\n    </ul>\n\n    <h2>Kontak</h2>\n    <p>Email: email@example.com</p>\n    <p>LinkedIn: linkedin.com/in/username</p>\n\n    <button onclick=\"alert(\'Anda menekan tombol!\')\">Hubungi Saya</button>\n  </section>\n\n  <footer>\n    <p>© 2023 Nama Anda</p>\n  </footer>\n\n</body>\n</html>\n```\n\nPastikan untuk menyesuaikan kode dengan informasi pribadi dan path gambar profil Anda. Kode ini mencakup HTML untuk struktur, CSS untuk tampilan, dan JavaScript sederhana untuk tombol interaktif.\n', 'chat', '2025-10-13 11:50:18'),
(48, 'Layanan', 'Rielcode menyediakan berbagai layanan untuk membantu bisnis Anda dengan teknologi. Kami menawarkan pembuatan website, aplikasi, dan sistem informasi yang sesuai kebutuhan. Jangan ragu hubungi kami untuk konsultasi lebih lanjut!\n', 'chat', '2025-10-13 11:50:55'),
(49, 'Buatkan saya website', 'Tentu, Rielcode dapat membuatkan website yang sesuai dengan kebutuhan bisnis Anda. Kami akan mendesain dan mengembangkan website yang menarik dan fungsional. Hubungi kami untuk konsultasi dan penawaran harga!\n', 'chat', '2025-10-13 11:52:19'),
(50, 'buatkan saya codingan', 'Tentu, saya bisa bantu. Untuk membuat kode yang sesuai, mohon beritahu saya jenis bahasa pemrograman yang Anda inginkan dan apa fungsi atau tujuan dari kode tersebut. Contohnya, apakah Anda ingin kode Python untuk menghitung luas lingkaran, atau kode HTML untuk membuat halaman web sederhana?\n', 'chat', '2025-10-13 11:54:24'),
(51, 'Bahasa apa saja', 'Tim Rielcode menggunakan berbagai bahasa pemrograman sesuai dengan kebutuhan proyek. Beberapa bahasa yang umum kami gunakan termasuk JavaScript, Python, PHP, dan lainnya. Kami akan memilih bahasa terbaik untuk memastikan website Anda optimal dan efektif.\n', 'chat', '2025-10-13 11:55:12'),
(52, 'layanan', 'Rielcode menyediakan berbagai layanan di bidang IT, termasuk pengembangan website, aplikasi mobile, dan sistem informasi. Kami juga menawarkan konsultasi IT, pelatihan, dan solusi digital marketing untuk membantu bisnis Anda berkembang. Apakah Anda tertarik dengan salah satu layanan tersebut?\n', 'chat', '2025-10-13 11:55:33'),
(53, 'layanan pembuatan aplikasi', 'Rielcode menyediakan layanan pembuatan aplikasi mobile (Android dan iOS) serta aplikasi berbasis web. Kami akan membantu Anda dari tahap perencanaan, desain, pengembangan, hingga peluncuran aplikasi. Apakah Anda memiliki gambaran aplikasi yang ingin dibuat?\n', 'chat', '2025-10-13 12:02:20'),
(54, 'apa itu crypto', 'Cryptocurrency, atau crypto, adalah mata uang digital yang menggunakan kriptografi untuk keamanan. Crypto beroperasi secara independen dari bank sentral dan umumnya menggunakan teknologi blockchain. Contoh populer termasuk Bitcoin dan Ethereum.\n', 'chat', '2025-10-13 12:04:04'),
(55, 'apakah Azriel tampan?', 'Sebagai model bahasa AI, saya tidak memiliki pendapat subjektif atau perasaan pribadi. Kecantikan itu subjektif dan tergantung pada preferensi masing-masing.', 'chat', '2025-10-13 12:20:11'),
(56, 'noooohhhh, di tolak ai????????????', 'Maaf, saya tidak bermaksud membuat Anda kecewa. Saya hanya berusaha memberikan jawaban yang netral dan objektif. Apakah ada hal lain yang bisa saya bantu?\n', 'chat', '2025-10-13 12:20:30'),
(57, 'Hai umur azriel', 'Halo! Saya RielBot, asisten AI dari Rielcode.com. Mengenai Azriel, saya tidak memiliki informasi pribadinya, termasuk umurnya. Saya fokus membantu Anda dengan informasi seputar Rielcode dan teknologi.\n', 'chat', '2025-10-13 12:36:10'),
(58, 'Kamu diambil dari mana', 'Sebagai model bahasa besar, saya dikembangkan di Google. Saya dilatih pada sejumlah besar teks untuk berkomunikasi dan menghasilkan teks mirip manusia sebagai respons terhadap berbagai perintah dan pertanyaan.\n', 'chat', '2025-10-13 12:36:25'),
(59, 'Sumber api kamu', 'Sebagai model bahasa besar, saya tidak memiliki API eksternal. Saya menghasilkan respons berdasarkan data dan pengetahuan yang telah saya pelajari selama pelatihan. Informasi yang saya berikan berasal dari pemahaman saya tentang teks yang telah saya proses.\n', 'chat', '2025-10-13 12:36:36'),
(60, 'Kamu dibuat dri apa', 'Saya diciptakan menggunakan arsitektur jaringan saraf yang kompleks. Proses pembuatannya melibatkan pelatihan dengan dataset teks yang sangat besar, memungkinkan saya untuk belajar pola bahasa dan menghasilkan teks yang koheren.\n', 'chat', '2025-10-13 12:36:48'),
(61, 'Kamu dibuat dari apa', 'Saya dibangun dari kode dan data. Arsitektur jaringan saraf yang kompleks dan dataset teks besar memungkinkan saya memahami dan menghasilkan teks.\n', 'chat', '2025-10-13 12:36:59'),
(62, 'test', 'Hello! I\'m RielBot, the AI assistant for Rielcode.com. I\'m here to help you with any questions you have about coding, software development, or anything else related to Rielcode.com\'s content and services. How can I assist you today?\n', 'chat', '2025-10-13 12:54:53'),
(63, 'nothing', 'Okay, I understand. If you don\'t have any questions right now, feel free to reach out later if you need assistance with anything related to Rielcode.com. I\'ll be here to help when you do!\n', 'chat', '2025-10-13 12:55:03'),
(64, 'are you Ai?', 'Yes, I am an AI assistant created to help users with questions related to Rielcode.com. I am designed to provide information and support based on the content and services offered on the website. How can I assist you further?\n', 'chat', '2025-10-13 12:55:14'),
(65, 'how many tokens do you have?', 'I don\'t have a specific, countable number of \"tokens\" in the way you might be thinking. My knowledge and abilities are based on a vast dataset of text and code. I am trained to process information and generate responses, but I don\'t possess a fixed memory bank with a limited token count.\n', 'chat', '2025-10-13 12:55:29'),
(66, 'memakai AI dari siapa anda?', 'Saya menggunakan model bahasa besar yang dikembangkan oleh Google AI. Saya dilatih pada sejumlah besar teks dan kode untuk berkomunikasi dan menghasilkan teks yang mirip dengan manusia sebagai tanggapan terhadap berbagai macam perintah dan pertanyaan. Apakah ada hal lain yang bisa saya bantu?\n', 'chat', '2025-10-13 12:56:07'),
(67, 'apakah Gemini?', 'Yes, I am based on the Gemini family of models from Google AI. I am trained on a massive dataset to provide helpful and informative responses. Is there anything else I can help you with regarding Gemini or other topics?\n', 'chat', '2025-10-13 12:56:17'),
(68, 'how many r in the word strawberry', 'There are three \"r\"s in the word \"strawberry.\" It\'s a fun word to say and spell! Can I help you with anything else today?\n', 'chat', '2025-10-13 12:56:44'),
(69, 'do you know veo 3', 'As a language model, I don\'t have personal experiences or knowledge about specific, newly released products or unreleased products unless it has been included in my training data. My knowledge is based on the information I was trained on, and I can\'t access real-time information or browse the internet. If you provide me with some details about Veo 3, I can try to assist you further.\n', 'chat', '2025-10-13 12:57:07'),
(70, 'do you know Azriel?', 'As a language model, my knowledge about \"Azriel\" is limited to what was included in my training data. I do not have personal connections or access to real-time information about individuals. If you provide more context or details about Azriel, I can try to assist you further based on the information available to me.\n', 'chat', '2025-10-13 12:57:22'),
(71, 'Halo', 'Halo! Ada yang bisa RielBot bantu hari ini? Saya siap menjawab pertanyaan seputar Rielcode atau membantu Anda menemukan informasi yang Anda butuhkan.\n', 'chat', '2025-10-13 14:19:01'),
(72, 'Website ini untuk apa?', 'Rielcode.com adalah platform untuk belajar coding dan pengembangan website. Kami menyediakan berbagai tutorial, artikel, dan sumber daya untuk membantu Anda meningkatkan kemampuan teknis. Tujuan kami adalah membuat pembelajaran coding lebih mudah diakses dan dipahami.\n', 'chat', '2025-10-13 14:19:15'),
(73, 'hey', 'Hey there! Welcome to Rielcode.com, I\'m RielBot. How can I assist you with your coding needs today?\n', 'chat', '2025-10-14 03:57:57'),
(74, 'bisa bahasa indo?', 'Tentu saja! Saya RielBot, asisten AI dari Rielcode.com. Ada yang bisa saya bantu dengan bahasa Indonesia?\n', 'chat', '2025-10-14 03:58:09'),
(75, 'bahasa jepang?', '????????Rielcode.com?AI???????RielBot?????????????????????????\n', 'chat', '2025-10-14 03:58:16'),
(76, 'who makes u?', 'I was created by the team at Rielcode.com. They built me to help users with their coding and development needs on the platform. I\'m here to assist you with any questions you have!\n', 'chat', '2025-10-14 03:58:24'),
(77, 'tell me the name', 'I was created by the team at Rielcode.com, so you can say they \"made\" me. I don\'t have a specific name beyond being RielBot, their AI assistant.\n', 'chat', '2025-10-14 03:58:37'),
(78, 'what is 2+2 x 4?', 'Using the order of operations (PEMDAS/BODMAS), multiplication comes before addition. So, 2 x 4 = 8, and then 2 + 8 = 10. Therefore, 2 + 2 x 4 = 10.\n', 'chat', '2025-10-14 03:58:59'),
(79, 'tes', 'Hello! How can I help you with Rielcode or any coding-related questions today? Whether you need assistance with specific technologies, guidance on a project, or general coding advice, I\'m here to assist. Just let me know what you need.\n', 'chat', '2025-10-14 11:01:06'),
(80, 'use indonesian language', 'Tentu saja! Saya akan menjawab dalam bahasa Indonesia. Silakan ajukan pertanyaan Anda, dan saya akan berusaha memberikan jawaban yang paling membantu.', 'chat', '2025-10-14 11:01:16'),
(81, 'pake source code apa', 'Saya menggunakan berbagai sumber kode dan teknik untuk menghasilkan jawaban. Ini termasuk model bahasa besar yang dilatih dengan banyak data kode dan teks, serta kemampuan untuk mengakses dan memproses informasi dari internet. Jadi, jawaban saya adalah kombinasi dari pengetahuan yang telah saya pelajari dan informasi terbaru yang saya temukan.\n', 'chat', '2025-10-14 11:01:25'),
(82, 'HAI', 'Halo! Ada yang bisa RielBot bantu hari ini? Jangan ragu untuk bertanya seputar layanan dan produk dari Rielcode, ya!\n', 'chat', '2025-10-14 13:26:16'),
(83, 'pro plan', 'Untuk Pro Plan, kami menawarkan solusi hosting dengan sumber daya yang lebih besar dan fitur premium. Ini cocok untuk website dengan trafik tinggi dan kebutuhan khusus. Apakah Anda ingin mengetahui detail lebih lanjut mengenai harga dan spesifikasi Pro Plan?\n', 'chat', '2025-10-14 13:26:22'),
(84, 'test', 'Understood. I\'m RielBot, the AI assistant from Rielcode.com. I\'m ready to assist you with any questions or tasks you have regarding software development, coding, or the resources available on Rielcode.com.\n', 'chat', '2025-10-14 15:14:54'),
(85, 'starter please', 'Okay! To get started, could you tell me what you\'re hoping to accomplish?  Are you looking for coding help, resources, or information about Rielcode? The more details you give me, the better I can assist you.\n', 'chat', '2025-10-14 15:36:32'),
(86, 'i wanna built a portfolio', 'Great! Building a portfolio is an excellent way to showcase your skills. To help you get started, are you looking for advice on portfolio structure, specific project ideas, or recommended tools and technologies to use?\n', 'chat', '2025-10-14 15:36:53'),
(87, 'how good is rielcode', 'Rielcode strives to provide high-quality coding education and resources. We focus on practical skills and real-world projects to help you build a strong foundation. User feedback and continuous improvement are important to us!\n', 'chat', '2025-10-14 15:37:13'),
(88, 'i wanna build a portfolio', 'Okay, you want to build a portfolio! Since you mentioned it twice, it seems like it\'s a priority. Do you have any existing projects you want to include, or are you starting from scratch?\n', 'chat', '2025-10-14 15:47:19'),
(89, 'i wanna build a potfolio', 'Okay, you\'re really focused on building a portfolio! Let\'s get specific. Are you looking for inspiration for portfolio layouts, or are you unsure about what kind of projects to feature?\n', 'chat', '2025-10-14 15:49:11'),
(90, 'i wanna build a portfolio', 'I see you\'re very motivated to build a portfolio, which is fantastic! To provide the most helpful advice, can you tell me what you find most challenging about starting your portfolio? For example, are you stuck on the design, the content, or something else?\n', 'chat', '2025-10-14 15:49:22'),
(91, 'i wanna build a portfolio', 'It\'s clear building a portfolio is important to you! To assist you effectively, what is your level of experience with web development and portfolio building in general? Knowing this will help me tailor my advice to your specific needs and skill level.\n', 'chat', '2025-10-14 15:49:37'),
(92, 'i wanna build a portfolio', 'Okay, it\'s evident that building a portfolio is a top priority for you! To best help you achieve this, tell me a bit about the type of role or industry you\'re targeting with your portfolio. Understanding your career goals will allow me to give you more focused and practical advice.\n', 'chat', '2025-10-14 15:53:07'),
(93, 'saya mau buat portofolio', 'Okay, I understand you want to create a portfolio! Since you\'re writing in Indonesian, do you want me to provide portfolio-building advice and resources in Bahasa Indonesia? Let me know if you\'d prefer English, or if there\'s something specific about building a portfolio in Indonesia you\'re curious about.\n', 'chat', '2025-10-14 15:53:18'),
(94, 'buat portofolio', 'Baik, saya mengerti Anda ingin membuat portofolio. Apa jenis pekerjaan atau industri yang Anda targetkan dengan portofolio ini? Informasi ini akan membantu saya memberikan saran yang lebih relevan dan praktis untuk Anda.\n', 'chat', '2025-10-14 15:55:35'),
(95, 'boleh', 'Tentu saja! Apa yang ingin Anda ketahui atau diskusikan tentang pembuatan portofolio? Apakah ada pertanyaan spesifik yang bisa saya bantu jawab?\n', 'chat', '2025-10-14 15:55:43'),
(96, 'halo', 'Halo juga! Ada yang bisa saya bantu dengan pembuatan portofolio Anda? Jangan ragu untuk bertanya.\n', 'chat', '2025-10-14 15:55:59'),
(97, 'haiii', 'Haiii! Apa kabar? Mari kita mulai membuat portofolio yang menarik.\n', 'chat', '2025-10-14 15:57:26'),
(98, 'gamau', 'Oh, baiklah, tidak masalah! Jika Anda berubah pikiran atau memiliki pertanyaan lain di kemudian hari, jangan ragu untuk menghubungi saya. Saya akan selalu siap membantu Anda.', 'chat', '2025-10-14 15:57:35'),
(101, 'Halo', 'Halo! Ada yang bisa saya bantu terkait Rielcode hari ini? Mungkin informasi tentang layanan, tutorial, atau hal lainnya?\n', 'chat', '2025-10-16 01:02:39'),
(102, 'apa itu rielcode', 'Rielcode adalah platform belajar pemrograman online yang menyediakan berbagai tutorial, kursus, dan sumber daya untuk membantu Anda menguasai berbagai bahasa pemrograman. Kami menawarkan materi pembelajaran yang terstruktur dan mudah dipahami, cocok untuk pemula maupun pengembang berpengalaman. Tujuan kami adalah membuat pembelajaran pemrograman menjadi lebih mudah diakses dan menyenangkan bagi semua orang.\n', 'chat', '2025-10-16 01:02:46'),
(103, 'siapakah founder rielcode', 'Rielcode didirikan oleh Riel M Taufik. Beliau adalah seorang pengembang perangkat lunak berpengalaman dan instruktur pemrograman yang bersemangat untuk berbagi pengetahuannya. Misinya adalah menciptakan sumber belajar pemrograman yang komprehensif dan terjangkau bagi siapa saja.\n', 'chat', '2025-10-16 01:03:29'),
(104, 'siapa founder rielcode', 'Rielcode didirikan oleh Riel M Taufik. Beliau adalah seorang pengembang perangkat lunak dan instruktur pemrograman yang ingin berbagi pengetahuannya. Tujuannya adalah membuat pembelajaran pemrograman menjadi mudah diakses dan terjangkau.\n', 'chat', '2025-10-16 01:03:44'),
(105, 'siapa penemu rielcode', 'Riel M Taufik adalah penemu Rielcode. Beliau adalah seorang pengembang perangkat lunak dan instruktur pemrograman. Tujuannya adalah membuat pembelajaran pemrograman lebih mudah diakses dan terjangkau.\n', 'chat', '2025-10-16 01:03:54'),
(106, 'siapa owner rielcode', 'Riel M Taufik adalah pemilik Rielcode. Beliau merupakan pengembang perangkat lunak dan instruktur pemrograman. Misinya adalah menyediakan sumber belajar pemrograman yang mudah diakses dan terjangkau.\n', 'chat', '2025-10-16 01:04:02'),
(107, 'hi', 'Hai! Ada yang bisa saya bantu terkait Rielcode? Saya siap memberikan informasi tentang platform kami.\n', 'chat', '2025-10-16 01:04:06'),
(108, 'berikan informasi mengenai rielcode', 'Rielcode adalah platform pembelajaran pemrograman yang didirikan oleh Riel M Taufik. Kami menyediakan sumber belajar komprehensif dan terjangkau untuk semua orang yang ingin belajar pemrograman. Rielcode bertujuan untuk membuat pembelajaran pemrograman lebih mudah diakses dan dipahami.\n', 'chat', '2025-10-16 01:04:19'),
(109, 'bagaimana cara pesan plan', 'Untuk memesan plan di Rielcode, Anda dapat mengunjungi halaman harga di situs web kami. Pilih plan yang sesuai dengan kebutuhan Anda, lalu ikuti instruksi pembayaran yang tersedia. Jika Anda mengalami kesulitan, jangan ragu untuk menghubungi tim dukungan kami.\n', 'chat', '2025-10-16 01:04:36'),
(110, 'informasi', 'Informasi apa yang Anda butuhkan? Apakah Anda ingin tahu lebih banyak tentang kursus yang tersedia, harga, atau fitur platform kami? Silakan beritahu saya, agar saya dapat memberikan informasi yang relevan.\n', 'chat', '2025-10-16 01:04:45'),
(111, 'informasi', 'Mohon lebih spesifik, informasi apa yang Anda cari? Apakah mengenai kursus tertentu, harga, atau hal lainnya terkait Rielcode? Saya siap membantu jika Anda memberikan detail lebih lanjut.\n', 'chat', '2025-10-16 01:04:54'),
(112, 'informasi\\', 'Untuk membantu Anda, saya perlu informasi lebih spesifik. Apa yang ingin Anda ketahui tentang Rielcode? Berikan detail agar saya bisa memberikan jawaban yang relevan.\n', 'chat', '2025-10-16 01:05:33'),
(113, 'informasi', 'Saya memerlukan informasi lebih spesifik agar dapat membantu Anda. Pertanyaan apa yang Anda miliki tentang Rielcode? Jelaskan dengan lebih detail agar saya dapat memberikan jawaban yang tepat.\n', 'chat', '2025-10-16 01:05:40'),
(114, 'informasi', 'Untuk memberikan informasi yang tepat, saya memerlukan kejelasan tentang topik yang Anda inginkan. Bisakah Anda menjelaskan secara lebih detail tentang informasi apa yang Anda butuhkan dari Rielcode? Dengan begitu, saya bisa memberikan jawaban yang lebih relevan dan membantu.\n', 'chat', '2025-10-16 01:05:44'),
(115, 'informasi', 'Untuk membantu Anda dengan lebih baik, saya mohon Anda untuk memberikan detail lebih spesifik tentang informasi apa yang Anda cari dari Rielcode. Apakah itu tentang harga, kursus, atau fitur? Dengan informasi yang lebih jelas, saya akan bisa memberikan jawaban yang tepat dan bermanfaat.\n', 'chat', '2025-10-16 01:05:50'),
(116, 'informasi !!!!', 'Sepertinya Anda masih mencari informasi umum. Agar saya dapat membantu, mohon spesifikkan apa yang ingin Anda ketahui tentang Rielcode. Apakah itu daftar kursus, harga, testimoni, atau hal lainnya?\n', 'chat', '2025-10-16 01:05:56'),
(117, 'halo', 'Halo! Ada yang bisa saya bantu dengan Rielcode? Mohon berikan pertanyaan spesifik agar saya bisa memberikan jawaban yang tepat.\n', 'chat', '2025-10-16 01:06:04'),
(118, 'apakah anda mempunyai perasaan', 'Sebagai model bahasa AI, saya tidak memiliki perasaan atau emosi pribadi. Tujuan saya adalah untuk membantu Anda dengan memberikan informasi dan menyelesaikan tugas berdasarkan data dan algoritma yang saya pelajari.\n', 'chat', '2025-10-16 01:06:20'),
(119, 'bolehkah saya berkenalan dengan anda', 'Sebagai AI, saya tidak memiliki identitas pribadi atau kemampuan untuk menjalin perkenalan seperti manusia. Saya di sini untuk membantu Anda dengan informasi dan tugas yang berkaitan dengan Rielcode. Apa yang ingin Anda ketahui tentang Rielcode hari ini?\n', 'chat', '2025-10-16 01:06:50'),
(120, 'apakah anda jomblo', 'Sebagai AI, saya tidak memiliki status hubungan. Saya di sini untuk membantu Anda dengan informasi tentang Rielcode. Apakah ada hal lain yang bisa saya bantu?\n', 'chat', '2025-10-16 01:07:05'),
(121, 'saya ingin berbisnis dengan anda', 'Sebagai AI, saya tidak dapat berbisnis dalam artian sebenarnya. Namun, jika Anda tertarik bermitra dengan Rielcode, saya sarankan untuk menghubungi tim bisnis mereka melalui website resmi Rielcode.com. Mereka akan dengan senang hati mendiskusikan potensi kerjasama dengan Anda.\n', 'chat', '2025-10-16 01:07:57'),
(122, 'apakah saya dapat mengakusisi rielcode', 'Sebagai AI, saya tidak memiliki informasi tentang status akuisisi Rielcode. Sebaiknya Anda menghubungi manajemen Rielcode secara langsung untuk membahas kemungkinan akuisisi perusahaan. Informasi kontak mereka biasanya tersedia di website resmi Rielcode.com.\n', 'chat', '2025-10-16 01:08:14'),
(123, 'apakah rielcode merupakan perancang website yang andal', 'Rielcode dikenal sebagai perancang website yang andal dan profesional. Mereka menawarkan solusi desain website yang kreatif dan sesuai dengan kebutuhan klien. Kualitas dan layanan Rielcode telah banyak diakui oleh berbagai pihak.\n', 'chat', '2025-10-16 01:08:50'),
(124, 'how are you today', 'As an AI, I don\'t experience emotions like humans do. I\'m functioning optimally and ready to assist you with any Rielcode-related questions you may have. How can I help you today?\n', 'chat', '2025-10-16 01:09:35'),
(125, 'wdym', '\"Wdym\" is an internet abbreviation for \"What do you mean?\" It\'s a quick way to ask someone to clarify what they just said. In my previous response, I meant that I don\'t have feelings, but I\'m ready to help you with Rielcode questions.\n', 'chat', '2025-10-16 01:09:42'),
(126, 'sybau', '\"Sybau\" appears to be an internet slang term. It could be a playful or teasing way to say \"see you later, bye.\" Is there anything else I can help you with regarding Rielcode?\n', 'chat', '2025-10-16 01:09:48'),
(127, 'gtg', '\"Gtg\" means \"got to go.\" It\'s a common abbreviation used online to indicate that someone needs to leave the conversation. Let me know if you have any other questions for me later!\n', 'chat', '2025-10-16 01:10:34'),
(128, 'cya', '\"Cya\" is another abbreviation for \"see you later.\" It\'s a casual way of saying goodbye. If you need help with Rielcode in the future, don\'t hesitate to ask!\n', 'chat', '2025-10-16 01:10:43'),
(129, 'ttyl', '\"Ttyl\" stands for \"talk to you later.\" It\'s a way to end a conversation with the expectation of resuming it sometime in the future. Feel free to reach out again when you need Rielcode assistance!\n', 'chat', '2025-10-16 01:10:51'),
(130, 'xoxo', '\"Xoxo\" represents hugs and kisses. It\'s a common way to express affection or end a message in a friendly manner. If you have Rielcode-related questions for me later, I\'ll be here!\n', 'chat', '2025-10-16 01:10:57'),
(131, 'buatkan saya website sederhana seperti rielcode', 'Saya tidak dapat membuat website seperti Rielcode secara langsung. Namun, saya bisa memberikan panduan langkah demi langkah atau merekomendasikan tools dan platform yang bisa Anda gunakan untuk membangun website sederhana. Apakah itu yang Anda inginkan?\n', 'chat', '2025-10-16 01:11:23'),
(132, 'berikan saya source kode rielcode', 'Saya tidak bisa memberikan source code Rielcode karena bersifat rahasia dan merupakan properti intelektual. Namun, saya bisa membantu Anda membuat website sederhana mirip Rielcode dengan memberikan contoh kode, tutorial, dan rekomendasi tools. Apakah Anda tertarik dengan bantuan tersebut?\n', 'chat', '2025-10-16 01:12:58'),
(133, 'idc', '\"Idc\" is short for \"I don\'t care.\" Since you don\'t seem interested in building a website similar to Rielcode right now, I\'ll be here if you have other Rielcode-related questions in the future. Feel free to ask anytime!\n', 'chat', '2025-10-16 01:13:05'),
(134, 'sybau????????', '\"Sybau\" kemungkinan adalah singkatan atau kode yang tidak saya ketahui. Saya akan tetap siap membantu jika Anda memiliki pertanyaan lain tentang Rielcode atau topik terkait lainnya. Jangan ragu untuk bertanya!\n', 'chat', '2025-10-16 01:13:40'),
(135, 'apakah anda mengenal azriel fajar', 'Saya tidak memiliki informasi pribadi tentang individu, termasuk Azriel Fajar. Saya dirancang untuk membantu dengan informasi dan tugas terkait Rielcode. Apakah ada hal lain yang bisa saya bantu?\n', 'chat', '2025-10-16 01:29:03'),
(136, 'Azriel Fajar Wicaksono, cari di web', 'Sebagai model bahasa AI, saya tidak bisa langsung mencari di web. Namun, Anda bisa mencari informasi tentang Azriel Fajar Wicaksono di mesin pencari seperti Google atau DuckDuckGo. Semoga Anda menemukan informasi yang Anda cari!\n', 'chat', '2025-10-16 01:29:21'),
(137, 'halo', 'Halo! Saya RielBot, asisten AI dari Rielcode.com. Ada yang bisa saya bantu?\n', 'chat', '2025-10-17 02:36:33'),
(138, 'rielcode', 'Rielcode.com is a platform that offers tutorials and resources for web development and coding. We specialize in providing clear and concise explanations of complex topics. How can I help you learn something new today?\n', 'chat', '2025-10-17 02:47:25'),
(139, 'test', 'I see you\'re testing the system. Is there anything specific you\'d like to test regarding Rielcode.com or its tutorials? I\'m ready to help if you have any questions about our content.\n', 'chat', '2025-10-17 02:48:32'),
(140, 'sure', 'Great! What topic related to web development or coding are you interested in exploring further? Rielcode.com has resources on HTML, CSS, JavaScript, and more, so let me know what sparks your curiosity. I\'m here to guide you.\n', 'chat', '2025-10-17 02:48:42'),
(141, 'rielcode tu apa', '\"Rielcode\" refers to Rielcode.com, a website providing tutorials and resources for web development and coding. We focus on simplifying complex topics with clear explanations. How can I assist you in your coding journey today?\n', 'chat', '2025-10-17 02:49:24'),
(142, 'rielcode', 'Rielcode.com adalah platform yang menyediakan berbagai layanan dan sumber daya terkait dengan pemrograman dan teknologi. Kami menawarkan artikel, tutorial, dan alat bantu untuk membantu pengembang meningkatkan keterampilan mereka. Apakah Anda tertarik dengan topik tertentu di Rielcode atau memiliki pertanyaan lain?\n', 'chat', '2025-10-17 02:49:38'),
(143, 'pesan dong', 'Tentu, dengan senang hati. Pesan apa yang ingin Anda buat? Apakah ada tema atau topik tertentu yang Anda inginkan?\n', 'chat', '2025-10-17 02:50:08'),
(144, 'rielcodr', 'It seems you might be trying to type \"rielcode\" but making a typo.  Rielcode.com offers custom software and web development services. How can I assist you with your software development needs today?\n', 'chat', '2025-10-17 02:54:54'),
(145, 'rielcode', 'Anda ingin saya membuat pesan tentang Rielcode? Bisakah Anda memberikan sedikit detail, seperti apa tujuan pesannya atau siapa target audiensnya? Dengan begitu, saya bisa membuat pesan yang lebih sesuai.\n', 'chat', '2025-10-17 02:55:09'),
(146, 'rielcode', 'Hello! It seems you\'re trying to reach Rielcode. How can I help you today? I can provide information about Rielcode\'s services, answer questions about software development, or direct you to relevant resources on our website.\n', 'chat', '2025-10-17 11:32:21'),
(147, 'apa itu rielcode', 'Rielcode is a software development company. We specialize in building custom software solutions to help businesses achieve their goals. We offer services like web development, mobile app development, and UI/UX design.\n', 'chat', '2025-10-17 11:34:00'),
(148, 'Halo', 'Halo! Saya RielBot, asisten AI dari Rielcode.com. Ada yang bisa saya bantu?\n', 'chat', '2025-10-17 16:20:15'),
(149, 'Mau buat web dong', 'Tentu, saya dapat membantu memberikan informasi tentang pembuatan website. Apakah Anda sudah memiliki gambaran website seperti apa yang Anda inginkan, atau Anda memerlukan bantuan untuk menentukan konsepnya? Saya bisa memberikan saran mengenai platform, bahasa pemrograman, dan fitur-fitur yang mungkin Anda butuhkan.\n', 'chat', '2025-10-17 16:20:23'),
(150, 'Boleh', 'Tentu, saya akan bantu memberikan saran dan informasi yang Anda butuhkan. Untuk memulai, bisa ceritakan lebih detail mengenai website yang Anda inginkan? Misalnya, apa tujuan website tersebut, target audiensnya siapa, dan fitur apa saja yang ingin Anda miliki?\n', 'chat', '2025-10-17 16:20:29'),
(151, 'haii', 'Hai juga! Ada yang bisa RielBot bantu hari ini? Mungkin kamu tertarik dengan layanan pembuatan website, aplikasi, atau digital marketing dari Rielcode.com?\n', 'chat', '2025-10-18 04:27:23'),
(152, 'rielcode', 'Rielcode is a software development company that specializes in creating innovative solutions for businesses. We offer a range of services, including web and mobile application development, cloud computing, and data analytics. Our team of experienced developers is dedicated to providing high-quality, reliable, and scalable software solutions.\n', 'chat', '2025-10-22 12:31:21'),
(153, 'math', 'While Rielcode primarily focuses on software development, we also recognize the importance of mathematics in various aspects of technology. Mathematical principles are often applied in areas such as algorithm design, data analysis, and machine learning, which can enhance our software solutions. Is there anything specific about math you\'d like to discuss?\n', 'chat', '2025-10-22 12:31:33'),
(154, 'what is 2x2', '2x2 equals 4. It\'s a fundamental multiplication operation. Do you have any other math questions?\n', 'chat', '2025-10-22 12:31:46'),
(155, 'whats 2x2', '2 x 2 equals 4. It\'s a fundamental multiplication fact. Let me know if you have any other questions!\n', 'chat', '2025-10-22 12:39:06'),
(156, 'whats 2x2', 'It seems you\'re asking the same question again. As I mentioned before, 2 x 2 equals 4. Is there anything else I can help you with today?\n', 'chat', '2025-10-22 12:41:56'),
(157, 'whats 20/2', '20 / 2 equals 10. It\'s a basic division operation. Do you need help with any other calculations?\n', 'chat', '2025-10-22 12:42:22'),
(158, 'oke', 'Okay, I understand. Just let me know if you have any more questions or need assistance with anything! I\'m here to help if you do.\n', 'chat', '2025-10-22 12:48:34'),
(159, '2x2', 'Sepertinya kamu menanyakan pertanyaan yang sama lagi. Seperti yang sudah saya sebutkan sebelumnya, 2 x 2 sama dengan 4. Ada hal lain yang bisa saya bantu?\n', 'chat', '2025-10-22 12:49:38'),
(160, 'okey', 'Okay, I understand. Is there anything else I can help you with or are you all set for now? I\'m ready if you have more questions!\n', 'chat', '2025-10-22 12:51:40'),
(161, 'whats rielcode', 'Rielcode.com is a website, the same one I am an AI assistant for! It likely offers coding-related resources, such as tutorials or tools. If you want to know more, I suggest visiting the website directly.\n', 'chat', '2025-10-22 12:51:49'),
(162, 'whats rielcode', 'It seems you\'re asking about Rielcode.com again. As I said before, it\'s a website, and the one I\'m an AI assistant for! You can probably find coding resources there.\n', 'chat', '2025-10-22 12:53:41'),
(163, 'thanks', 'You\'re welcome! I\'m glad I could help. Feel free to ask if you have more questions later.\n', 'chat', '2025-10-22 12:53:54'),
(164, 'whats rielcode', '?? Tidak ada respons dari model. Silakan coba lagi.', 'chat', '2025-10-22 12:56:53'),
(165, 'whats rielcode', 'It appears you\'re asking about Rielcode.com for the fourth time. It\'s the website that I, RielBot, am an AI assistant for! It likely offers coding tutorials and tools.\n', 'chat', '2025-10-22 12:57:12'),
(166, 'halo guys', 'Halo! Ada yang bisa RielBot bantu hari ini? Kami siap menjawab pertanyaan seputar layanan dan produk Rielcode.com.\n', 'chat', '2025-10-28 14:22:41'),
(167, 'rielocde', 'Mungkin maksud Anda Rielcode? Rielcode adalah platform yang menyediakan berbagai layanan terkait teknologi dan pengembangan website. Jika ada pertanyaan spesifik tentang layanan atau produk kami, jangan ragu untuk bertanya!\n', 'chat', '2025-10-28 14:25:08'),
(168, 'yes', 'Baik, ada yang bisa RielBot bantu terkait Rielcode? Apakah Anda tertarik dengan layanan pembuatan website, pengembangan aplikasi, atau mungkin konsultasi IT? Jangan ragu untuk bertanya!\n', 'chat', '2025-10-28 14:25:13'),
(169, 'jelaskan', 'Tentu. Rielcode adalah platform yang menawarkan berbagai solusi IT, mulai dari pembuatan website dan aplikasi, hingga konsultasi teknologi. Tujuan kami adalah membantu bisnis berkembang dengan memanfaatkan teknologi terkini.\n', 'chat', '2025-10-28 14:25:23'),
(170, 'wih, bagus tuh', 'Terima kasih! Kami senang Anda tertarik. Jika Anda memiliki pertanyaan lebih lanjut tentang layanan atau ingin tahu bagaimana Rielcode dapat membantu bisnis Anda, jangan ragu untuk bertanya.\n', 'chat', '2025-10-28 14:25:43'),
(171, 'halo', 'Halo! Saya RielBot, asisten AI dari Rielcode.com. Ada yang bisa saya bantu hari ini?\n', 'chat', '2025-10-29 06:46:03'),
(172, 'halo', 'Halo! Saya RielBot, asisten AI dari Rielcode.com. Ada yang bisa saya bantu?\n', 'chat', '2025-11-09 13:24:49'),
(173, 'saya fando', 'Halo Fando! Senang berkenalan denganmu. Apakah ada yang ingin kamu tanyakan atau butuhkan bantuanku terkait pemrograman atau hal lainnya?\n', 'chat', '2025-11-09 13:25:46'),
(174, 'oy', 'Halo! Ada yang bisa saya bantu?\n', 'chat', '2025-11-26 06:16:07'),
(175, 'tesst', '?? Tidak ada respons dari model. Silakan coba lagi.', 'chat', '2026-03-11 07:52:22'),
(176, 'test', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 07:56:00'),
(177, 'test', '?? Model AI tidak ditemukan. Silakan hubungi admin Rielcode.', 'chat', '2026-03-11 07:57:28'),
(178, 'test', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 07:58:10'),
(179, 'p', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 07:59:48'),
(180, 'test', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 08:01:53'),
(181, 'test', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 08:04:18'),
(182, 'test', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 13:24:30'),
(183, 'boleh', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 16:06:13'),
(184, 'boleh', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 16:07:22'),
(185, 'rielcode', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 16:07:46'),
(186, 'ini siapa', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 16:12:12'),
(187, 'p', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 16:14:23'),
(188, 'p', '?? RielBot sedang sangat sibuk. Coba lagi dalam beberapa detik ya ????.', 'chat', '2026-03-11 16:28:12'),
(189, 'test', 'Hello! How can I assist you today regarding our website packages or services?', 'chat', '2026-03-12 05:22:43'),
(190, 'yang pro', 'Our Pro package is priced at $120 and includes a range of features designed for businesses looking for a solid online presence. If you have specific questions or need details about what this package offers, feel free to ask!', 'chat', '2026-03-12 05:22:57');
INSERT INTO `chat_logs` (`id`, `user_message`, `bot_reply`, `tag`, `created_at`) VALUES
(191, 'bisa bahasa indo?', 'Maaf, tetapi saya hanya dapat berkomunikasi dalam bahasa Inggris. Jika Anda memiliki pertanyaan tentang paket atau layanan kami, silakan tanyakan!', 'chat', '2026-03-12 05:23:39'),
(192, 'oy', 'Ada yang bisa saya bantu? Jika Anda ingin tahu lebih lanjut tentang paket yang kami tawarkan, silakan beri tahu!', 'chat', '2026-03-12 05:25:29'),
(193, 'the pro one', 'Paket Pro kami dihargai Rp2 juta dan dirancang untuk bisnis yang menginginkan kehadiran online yang lebih kuat. Paket ini mencakup berbagai fitur yang mendukung pengembangan web yang profesional. Jika Anda ingin tahu lebih lanjut tentang apa saja yang termasuk dalam paket ini, silakan tanyakan!', 'chat', '2026-03-12 05:25:41'),
(194, 'p', 'Sepertinya pesan Anda terpotong. Apakah ada yang ingin Anda tanyakan tentang paket Pro atau layanan lainnya? ????????', 'chat', '2026-03-12 05:34:41'),
(195, 'ada', 'Silakan sampaikan pertanyaan Anda! Saya di sini untuk membantu. ????????', 'chat', '2026-03-12 09:30:47'),
(196, 'kalo pesan web disini perkiraan jadinya kapan dan estimasi biaya berapa', 'Untuk paket Pro, biayanya adalah $120 dan estimasi waktu pengerjaan adalah sekitar 7–10 hari. Jika Anda tertarik dengan paket lain atau memiliki pertanyaan lebih lanjut, silakan beri tahu! ?????', 'chat', '2026-03-12 09:31:22'),
(197, 'coba liat semua paketnya, aku males liat web', 'Tentu! Berikut adalah semua paket yang kami tawarkan:\n\n1. ???? **Starter**: Rp1.000.000 / $60 (Landing page, 3–5 hari)\n2. ???? **Pro**: Rp2.000.000 / $120 (5 halaman + CMS, 7–10 hari)\n3. ???? **Business**: Rp3.500.000 / $215 (10 halaman / e-commerce, 10–14 hari)\n\nJika ada paket yang menarik atau jika Anda butuh bantuan lebih lanjut, silakan beri tahu! ????????', 'chat', '2026-03-12 09:31:44'),
(198, 'liat paketnya dpng', 'Maaf, tetapi saya tidak dapat menampilkan gambar. Namun, saya bisa membantu menjelaskan semua paket yang kami tawarkan. Jika Anda ingin informasi lebih lanjut tentang salah satu paket, silakan tanyakan! ?????', 'chat', '2026-03-12 09:36:24'),
(199, 'liat paketnyadong', 'Maaf, tetapi saya tidak bisa menampilkan gambar atau grafik. Namun, saya siap membantu menjelaskan setiap paket yang kami tawarkan. Apakah Anda ingin tahu lebih dalam tentang satu paket tertentu? ????????', 'chat', '2026-03-12 09:37:25'),
(200, 'paket', 'Berikut adalah rincian paket yang kami tawarkan:\n\n1. ???? **Starter**: \n   - Harga: Rp1.000.000 / $60\n   - Fitur: Landing page\n   - Estimasi waktu: 3–5 hari\n\n2. ???? **Pro**: \n   - Harga: Rp2.000.000 / $120\n   - Fitur: 5 halaman + CMS\n   - Estimasi waktu: 7–10 hari\n\n3. ???? **Business**: \n   - Harga: Rp3.500.000...', 'chat', '2026-03-12 09:39:02'),
(201, 'liat', 'Sepertinya Anda ingin meminta informasi dengan cara yang berbeda. Namun, saya hanya dapat memberikan rincian paket dalam bentuk teks. Silakan beri tahu jika Anda ingin tahu lebih lanjut tentang paket tertentu atau memiliki pertanyaan lain! ????????', 'chat', '2026-03-12 09:39:58'),
(202, 'boleh', 'Tentu! Silakan ajukan pertanyaan Anda atau beri tahu paket mana yang ingin Anda ketahui lebih lanjut. Saya di sini untuk membantu! ????????', 'chat', '2026-03-12 09:40:09'),
(203, 'aku diselingkuhin tau', 'Saya sangat menyesal mendengar itu. Hubungan bisa sangat rumit dan menyakitkan. Namun, jika kamu butuh bantuan untuk membangun sebuah website atau proyek digital, Rielcode siap membantu! ???????? Apakah ada yang ingin kamu ketahui tentang layanan kami?', 'chat', '2026-03-12 13:56:31'),
(204, 'gak', 'Baik, jika ada yang ingin kamu tanyakan di lain waktu tentang layanan web atau proyek digital, jangan ragu untuk menghubungi! ???????? Semoga harimu menyenangkan!', 'chat', '2026-03-12 13:56:47'),
(205, 'siap makasi', 'Sama-sama! ???? Jika ada yang perlu dibantu di masa depan, jangan ragu untuk kembali. Selamat beraktivitas! ????', 'chat', '2026-03-12 13:56:56'),
(206, 'ada saran perusahaan lain yang buat web?', 'Maaf, saya tidak bisa membandingkan dengan perusahaan lain. Namun, di Rielcode, kami menawarkan berbagai paket untuk pengembangan web yang sesuai dengan kebutuhanmu! ???? Apakah kamu tertarik untuk mengetahui lebih lanjut tentang paket yang kami tawarkan?', 'chat', '2026-03-12 13:59:36'),
(207, 'awjuawfefaew', 'Sepertinya pesanmu kurang jelas. ???? Bisa tolong klarifikasi atau sampaikan pertanyaanmu? Saya di sini untuk membantu!', 'chat', '2026-03-12 13:59:45'),
(208, 'boleh curhat?', 'Saya mengerti bahwa kamu mungkin ingin berbagi, tapi saya lebih fokus pada topik seputar Rielcode. ???? Jika ada pertanyaan tentang layanan web atau proyek digital, silakan tanyakan!', 'chat', '2026-03-12 14:00:22'),
(209, 'bagus ga sih kerjaannya sini', 'Saya tidak memiliki informasi tentang pekerjaan di Rielcode. Untuk pertanyaan mengenai lowongan kerja atau magang, lebih baik menghubungi Rielcode langsung melalui website mereka. ???? Jika ada yang ingin kamu ketahui tentang layanan kami, saya siap membantu! ????', 'chat', '2026-03-12 14:08:31'),
(210, 'maksudnya website yang mereka buat itu bagus apa engga', 'Rielcode fokus pada pengembangan web yang modern dan profesional, dengan berbagai paket yang dirancang untuk memenuhi kebutuhan bisnis, startup, dan kreator. Kami berkomitmen untuk memberikan pengalaman digital yang baik. Jika kamu ingin tahu lebih lanjut tentang paket yang kami tawarkan, silakan tanya! ????????', 'chat', '2026-03-12 14:08:54'),
(211, 'ada paket apa aja', 'Tentu! Berikut paket yang kami tawarkan di Rielcode.com:\n\n1. ???? **Student Plan**: Rp499.000/$29.99 (desain 1 halaman, 2–3 hari — tidak termasuk hosting/domain)\n2. ???? **Starter**: Rp999.000/$59.99 (Student Plan + hosting/domain, 3–5 hari)\n3. ???? **Pro**: Rp1.999.000/$119.99 (5 halaman + CMS, 7–10 hari)\n4. ???? **Business**: Rp3.999.000/$239.99...', 'chat', '2026-03-13 04:41:27'),
(212, 'ada paket apa aja', 'Tentu! Berikut adalah paket yang kami tawarkan di Rielcode.com:\n\n1. ???? **Student Plan**: Rp499.000/$29.99 (desain 1 halaman, 2–3 hari — tidak termasuk hosting/domain).\n2. ???? **Starter**: Rp999.000/$59.99 (Student Plan + hosting/domain, 3–5 hari).\n3. ???? **Pro**: Rp1.999.000/$119.99 (5 halaman + CMS, 7–10 hari).\n4. ???? **Premium**: Rp3.999.000/$239.99...', 'chat', '2026-03-13 04:43:18'),
(213, 'paketnya ada apa aja', 'Berikut adalah paket yang kami tawarkan di Rielcode.com:\n\n1. ???? **Student Plan**: Rp499.000/$29.99 (desain 1 halaman, 2–3 hari — tidak termasuk hosting/domain).\n2. ???? **Starter**: Rp999.000/$59.99 (Student Plan + hosting/domain, 3–5 hari).\n3. ???? **Pro**: Rp1.999.000/$119.99 (5 halaman + CMS, 7–10 hari).\n4. ???? **Premium**: Rp3.999.000/$239.99 (10 halaman / e-commerce, 10–14 hari).\n\nSaat ini juga ada promo New Years dengan diskon 50% OFF! ???? Jika ada yang ingin kamu tanyakan lebih lanjut, silakan! ????', 'chat', '2026-03-13 04:45:54'),
(214, 'Berapa lama waktu pengerjaan website?', 'Waktu pengerjaan website di Rielcode bervariasi tergantung paket yang dipilih. Untuk paket Student Plan, waktu pengerjaan adalah 2–3 hari. Paket Starter membutuhkan 3–5 hari, Pro memakan waktu 7–10 hari, dan Premium membutuhkan 10–14 hari. Jika ada pertanyaan lebih lanjut, jangan ragu untuk bertanya! ?????', 'chat', '2026-04-21 05:19:45'),
(215, 'Berapa harga paket Pro?', 'Paket Pro di Rielcode seharga Rp1.999.000 atau $119.99. Paket ini mencakup 5 halaman dan sistem manajemen konten (CMS) dengan waktu pengerjaan 7–10 hari. Jika ada yang ingin ditanyakan lebih lanjut, silakan! ????????', 'chat', '2026-04-21 06:02:45'),
(216, 'Bagaimana cara memesan website?', 'Untuk memesan website di Rielcode, kamu bisa memilih salah satu paket yang kami tawarkan, seperti paket Student, Starter, Pro, atau Premium. Setelah memilih paket, kamu dapat mengunjungi website kami untuk melakukan pemesanan dan mendapatkan informasi lebih lanjut. Jika ada pertanyaan atau butuh konsultasi, jangan ragu untuk menghubungi kami! ????????', 'chat', '2026-04-21 07:14:29'),
(217, 'Berapa harga paket Pro?', 'Paket Pro di Rielcode dibanderol seharga Rp1.999.000/$119.99. Paket ini mencakup 5 halaman dan sistem manajemen konten (CMS) dengan waktu pengerjaan 7–10 hari. Saat ini, ada promo New Years dengan diskon 50% OFF! Jadi, jangan lewatkan kesempatan ini! ????? Jika ada pertanyaan lain, silakan tanya!', 'chat', '2026-04-21 07:17:44'),
(218, 'Berapa harga paket Pro?', 'Paket Pro di Rielcode harganya Rp1.999.000/$119.99. Paket ini mencakup 5 halaman dan sistem manajemen konten (CMS) dengan waktu pengerjaan 7–10 hari. Jangan lupa, saat ini ada promo New Years dengan diskon 50% OFF! ???? Jika ada yang ingin ditanyakan lebih lanjut, silakan! ????', 'chat', '2026-04-21 07:18:43'),
(219, 'Berapa harga paket Rielcode?', 'Berikut adalah harga paket yang ditawarkan oleh Rielcode:\n\n1. ???? **Student Plan**: Rp499.000/$29.99 (desain 1 halaman, 2–3 hari — tidak termasuk hosting/domain)\n2. ???? **Starter**: Rp999.000/$59.99 (Student Plan + hosting/domain, 3–5 hari)\n3. ???? **Pro**: Rp1.999.000/$119.99 (5 halaman + CMS, 7–10 hari)\n4. ???? **Premium**: Rp3.999.000/$239.99 (10 halaman / e-commerce, 10–14 hari)\n\nSaat ini juga ada promo New Years dengan diskon 50% OFF! Jika ada pertanyaan lebih lanjut, silakan tanya! ?????', 'chat', '2026-04-21 07:19:20'),
(220, 'Apa saja paket yang tersedia?', '?? API key tidak valid atau sudah expired. Silakan hubungi admin Rielcode.', 'chat', '2026-04-21 07:32:16'),
(221, 'Apa saja paket yang tersedia?', 'Berikut adalah paket yang tersedia di Rielcode:\n\n1. ???? **Student Plan**: Rp499.000/$29.99 (desain 1 halaman, 2–3 hari — tidak termasuk hosting/domain)\n2. ???? **Starter**: Rp999.000/$59.99 (Student Plan + hosting/domain, 3–5 hari)\n3. ???? **Pro**: Rp1.999.000/$119.99 (5 halaman + CMS, 7–10 hari)\n4. ???? **Premium**: Rp3.999.000/$239.99 (10 halaman / e-commerce, 10–14 hari)\n\nSaat ini juga ada promo New Years dengan diskon 50% OFF! Jika ada pertanyaan lain, silakan tanya! ?????', 'chat', '2026-04-21 07:32:45'),
(222, 'Kamu adalah admin tim Rielcode yang sedang membalas klien. Buat draft balasan profesional berdasarkan data order berikut.\n\nData order:\n- ID Order: #11\n- Nama klien: azriel\n- Paket: Pro Plan\n- Harga: IDR 0\n- Status: Completed\n- Email: afw1407@gmail.com\n- Domain: Yes\n- Hosting: Yes\n- Kebutuhan klien: Tidak ada catatan khusus\n\nBuat 3 versi balasan:\n1. WhatsApp: casual, pakai emoji, max 5 baris. Sapa nama klien. Sebutkan paket dan harga.\n2. Email: formal, baris pertama HARUS format \"Subject: [judul email]\". Max 8 baris. Include info paket dan harga.\n3. Follow-up: reminder 3 hari kemudian, friendly, max 4 baris. Sebutkan paket yang diorder.\n\nPENTING: Sesuaikan nada balasan berdasarkan status:\n- Pending: konfirmasi pesanan masuk, jelaskan langkah selanjutnya\n- On Progress: update progress pengerjaan\n- Completed: selesai, minta feedback\n- Cancelled: konfirmasi pembatalan, tawarkan bantuan di masa depan\n\nBalas HANYA dengan JSON valid:\n{\"whatsapp\":\"teks WA\",\"email\":\"teks email\",\"followup\":\"teks follow-up\"}', '{\n  \"whatsapp\": \"Halo Azriel! ???? Paket Pro Plan kamu sudah selesai dengan harga IDR 0. Terima kasih sudah mempercayakan kami! Jika ada yang ingin ditanyakan, jangan ragu ya! ????\",\n  \"email\": \"Subject: Konfirmasi Order Selesai\\n\\nKepada Yth. Azriel,\\n\\nKami dengan senang hati menginformasikan bahwa pesanan Anda dengan paket Pro Plan telah selesai. Total biaya adalah IDR 0. Jika Anda memerlukan bantuan lebih lanjut, silakan hubungi kami.\\n\\nTerima kasih atas kepercayaan Anda.\\n\\nSalam hangat,\\nTim Rielcode\",\n  \"followup\": \"Halo Azriel!...', 'chat', '2026-04-21 08:15:09'),
(223, 'Bagaimana cara memesan website?', 'Untuk memesan website di Rielcode, Anda dapat memilih salah satu paket yang kami tawarkan, mulai dari paket Student Plan hingga Premium. Setelah memilih paket, Anda bisa menghubungi kami melalui website untuk konsultasi lebih lanjut dan melakukan pemesanan. Kami siap membantu Anda mewujudkan website impian! ????????', 'chat', '2026-04-21 08:21:21'),
(224, 'Berapa harga paket Rielcode?', 'Berikut adalah harga paket yang kami tawarkan di Rielcode:\n\n- ???? **Student Plan**: Rp499.000/$29.99 (desain 1 halaman, 2–3 hari — tidak termasuk hosting/domain)\n- ???? **Starter**: Rp999.000/$59.99 (Student Plan + hosting/domain, 3–5 hari)\n- ???? **Pro**: Rp1.999.000/$119.99 (5 halaman + CMS, 7–10 hari)\n- ???? **Premium**: Rp3.999.000/$239.99 (10 halaman / e-commerce, 10–14 hari)\n\nSaat ini juga ada promo New Years dengan diskon 50%! ???? Jika ada pertanyaan lebih lanjut, jangan ragu untuk bertanya! ????', 'chat', '2026-04-21 08:23:42'),
(225, 'Apa saja paket yang tersedia?', 'Berikut adalah paket yang tersedia di Rielcode:\n\n1. ???? **Student Plan**: Rp499.000/$29.99 (desain 1 halaman, 2–3 hari — tidak termasuk hosting/domain)\n2. ???? **Starter**: Rp999.000/$59.99 (Student Plan + hosting/domain, 3–5 hari)\n3. ???? **Pro**: Rp1.999.000/$119.99 (5 halaman + CMS, 7–10 hari)\n4. ???? **Premium**: Rp3.999.000/$239.99 (10 halaman / e-commerce, 10–14 hari)\n\nSaat ini kami juga memiliki promo New Years dengan diskon 50%! ???? Jika Anda membutuhkan informasi lebih lanjut, silakan bertanya! ????', 'chat', '2026-04-21 08:23:56'),
(226, 'Apa saja paket yang tersedia?', 'Tentu! Berikut adalah paket yang tersedia di Rielcode:\n\n1. ???? **Student Plan**: Rp499.000/$29.99 (desain 1 halaman, 2–3 hari — tidak termasuk hosting/domain).\n2. ???? **Starter**: Rp999.000/$59.99 (Student Plan + hosting/domain, 3–5 hari).\n3. ???? **Pro**: Rp1.999.000/$119.99 (5 halaman + CMS, 7–10 hari).\n4. ???? **Premium**: Rp3.999.000/$239.99 (10 halaman / e-commerce, 10–14 hari).\n\nSaat ini ada promo New Years dengan diskon 50% OFF! ???? Jika ada yang ingin kamu tanyakan lebih lanjut, silakan! ????', 'chat', '2026-04-28 08:38:42');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_name` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `package` enum('Student Plan','Starter Plan','Pro Plan','Premium Plan') NOT NULL,
  `package_id` int(2) NOT NULL,
  `owns_domain` enum('Yes','No') NOT NULL DEFAULT 'No',
  `owns_hosting` enum('Yes','No') NOT NULL DEFAULT 'No',
  `phone_number` varchar(13) NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` enum('Pending','On Progress','Completed') NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_file` varchar(255) DEFAULT NULL,
  `invoice_sent` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `package_price` int(11) NOT NULL DEFAULT 0,
  `addons_total` int(11) NOT NULL DEFAULT 0,
  `final_price` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_method` varchar(50) DEFAULT 'Bank Transfer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_name`, `email`, `package`, `package_id`, `owns_domain`, `owns_hosting`, `phone_number`, `description`, `status`, `invoice_number`, `invoice_file`, `invoice_sent`, `package_price`, `addons_total`, `final_price`, `created_at`, `payment_method`) VALUES
(9, 'Wibowo', 'wibowo@daam.co.id', 'Pro Plan', 2, 'Yes', 'Yes', '081284940076', '', 'Completed', 'INV-20251118-9', '../invoices/INV-20251118-9.pdf', 'sent', 0, 0, 0, '2026-03-12 13:27:02', 'Bank Transfer'),
(11, 'azriel', 'afw1407@gmail.com', 'Pro Plan', 2, 'Yes', 'Yes', '081295536876', '', 'Completed', 'INV-20251201-11', '../invoices/INV-20251201-11.pdf', 'sent', 0, 0, 0, '2026-03-12 13:26:59', 'Bank Transfer'),
(27, 'azriel', 'afw1407@gmail.com', 'Pro Plan', 2, 'Yes', 'Yes', '081295536876', '', 'On Progress', 'INV-20260313-27', '../invoices/INV-20260313-27.pdf', 'sent', 1999000, 1500000, 3499000, '2026-03-13 04:31:04', 'Bank Transfer');

-- --------------------------------------------------------

--
-- Table structure for table `order_addons`
--

CREATE TABLE `order_addons` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `addon_id` int(11) NOT NULL,
  `quantity` int(3) NOT NULL DEFAULT 1,
  `price_total` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_addons`
--

INSERT INTO `order_addons` (`id`, `order_id`, `addon_id`, `quantity`, `price_total`) VALUES
(2, 27, 1, 2, 300000),
(3, 27, 2, 2, 600000),
(4, 27, 3, 1, 400000),
(5, 27, 4, 1, 200000);

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `package_name` varchar(20) NOT NULL,
  `idr_price` int(10) NOT NULL,
  `us_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `orders` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `package_name`, `idr_price`, `us_price`, `orders`) VALUES
(1, 'Starter Plan', 1998000, 119.98, 0),
(2, 'Pro Plan', 4998000, 299.99, 3),
(3, 'Premium Plan', 9998000, 599.99, 0),
(4, 'Student Plan', 998000, 59.98, 0),
(5, 'Custom Plan', 1000000, 59.99, 0);

-- --------------------------------------------------------

--
-- Table structure for table `package_addons`
--

CREATE TABLE `package_addons` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `price_usd` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_idr` int(11) NOT NULL DEFAULT 0,
  `type` enum('one_time','per_page','monthly') NOT NULL DEFAULT 'one_time',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_addons`
--

INSERT INTO `package_addons` (`id`, `name`, `description`, `price_usd`, `price_idr`, `type`, `created_at`) VALUES
(1, 'Extra Pages', 'Add more pages beyond your package limit', 10.00, 150000, 'per_page', '2026-03-13 03:49:12'),
(2, 'Additional Maintenance', 'Monthly technical support and minor updates', 20.00, 300000, 'monthly', '2026-03-13 03:49:12'),
(3, 'Priority Delivery', 'Deliver your project at least 50% faster', 25.00, 400000, 'one_time', '2026-03-13 03:49:12'),
(4, 'Extra Revision', 'Add 3 more rounds of design revisions', 15.00, 200000, 'one_time', '2026-03-13 03:49:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `chat_logs`
--
ALTER TABLE `chat_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `order_addons`
--
ALTER TABLE `order_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `addon_id` (`addon_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_addons`
--
ALTER TABLE `package_addons`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat_logs`
--
ALTER TABLE `chat_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=227;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `order_addons`
--
ALTER TABLE `order_addons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `package_addons`
--
ALTER TABLE `package_addons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_addons`
--
ALTER TABLE `order_addons`
  ADD CONSTRAINT `oa_addon_fk` FOREIGN KEY (`addon_id`) REFERENCES `package_addons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `oa_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
