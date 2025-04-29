<!DOCTYPE html>  
<html lang="id">  
<head>  
   <meta charset="UTF-8">  
   <meta name="viewport" content="width=device-width, initial-scale=1.0">  
   <meta name="csrf-token" content="{{ csrf_token() }}">  
   <title>HRIS Diskominfostandi Bekasi</title>  
   <link rel="icon" href="{{ asset('images/logo-diskominfostandi.png') }}" type="image/png">
   <link rel="shortcut icon" href="{{ asset('images/logo-diskominfostandi.png') }}" type="image/png">
   <script src="https://cdn.tailwindcss.com"></script>  
</head>  
<body class="bg-gray-100">  

<section class="relative bg-gradient-to-br from-blue-100 to-blue-500 h-screen w-full flex items-center justify-center">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black opacity-50"></div>

    <!-- Logo di pojok kiri atas -->
    <div class="absolute top-4 left-4 flex items-center space-x-4 z-50">
        <img src="/images/logo-bekasi.png" alt="Logo Pemerintah Kota Bekasi" class="h-12">
        <img src="/images/logo-diskominfostandi.png" alt="Logo Diskominfostandi Bekasi" class="h-12">
    </div>

    <!-- Konten utama -->
    <div class="relative container mx-auto px-6 flex justify-between items-center">
        <!-- Teks sebelah kiri -->
        <div class="w-1/2 text-white pr-12">
            <img src="/images/bg.png" alt="bg" class="mb-4">
            <h1 class="text-3xl md:text-4xl font-extrabold mb-4 typing"></h1>
            <h2 class="text-2xl md:text-3xl font-semibold typing"></h2>
            <p class="mt-4 text-base typing max-w-xl"></p>
        </div>

        <!-- Form login sebelah kanan -->
        <div class="w-1/2 max-w-md">
            <div class="bg-white bg-opacity-90 backdrop-blur-lg p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-center text-blue-900 mb-6">Human Resource<br>Information System</h2>
                <form action="/login" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" required
                            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password" required
                            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Ingat Saya</label>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-800 focus:outline-none">
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="absolute bottom-0 left-0 w-full text-white py-4">
        <div class="container mx-auto text-center">
            <p class="mb-0">  
                <small>&copy; <span id="year"></span> HRIS Diskominfostandi Bekasi. All rights reserved.</small>  
            </p>
            <small>Created for thesis purposes</small>
        </div>
    </footer>
</section>

<script>  
   document.addEventListener('DOMContentLoaded', function() {  
       const texts = {  
           h1: "Selamat Datang di HRIS",  
           h2: "Diskominfostandi Bekasi",  
           p: "Sistem yang dirancang untuk pengelolaan sumber daya manusia yang lebih modern dan efisien."  
       };  
       
       const elements = {  
           h1: document.querySelector('h1'),  
           h2: document.querySelector('h2'),  
           p: document.querySelector('p')  
       };  

       Object.keys(elements).forEach(key => {  
           elements[key].textContent = '';  
       });  

       function typeWriter(element, text, speed = 100) {  
           return new Promise(resolve => {  
               let i = 0;  
               const timer = setInterval(() => {  
                   if (i < text.length) {  
                       element.textContent += text.charAt(i);  
                       i++;  
                   } else {  
                       clearInterval(timer);  
                       resolve();  
                   }  
               }, speed);  
           });  
       }  

       async function animateText() {  
           await typeWriter(elements.h1, texts.h1);  
           await typeWriter(elements.h2, texts.h2);  
           await typeWriter(elements.p, texts.p, 50);  
       }  

       animateText();  
   });  

   document.getElementById("year").textContent = new Date().getFullYear();  
</script>  

</body>  
</html>