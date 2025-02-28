<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="https://nginovasi.com/asset/img/Logo%20NGI.svg">
    <title>Share Bus Position</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <link rel="stylesheet" href="<?= base_url('public/css/leaflet-rm.css') ?>">
    <link rel="stylesheet" href="//unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="<?= base_url('public/css/loading.css') ?>" />
    
    <style>
        /* Custom styles to ensure the map and widget are correctly layered */
        #map {
            height: 100vh;
            width: 100%;
        }

        /* Widget layering on top of map */
        .leaflet-container {
            z-index: 0;
        }

        .widget {
            z-index: 1000; /* Ensure the widget appears over the map */
        }
        p {
            font-size: 12px;
        }

        .rotating-marker {
            transform-origin: center;
            will-change: transform;
        }
        
        .leaflet-left .leaflet-control {
            font-size: 12px;
        }
        .change-flight-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .leaflet-div-icon {
            background: transparent;
            border: none;
        }

        .line-container {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            height: 40px;
        }

        .horizontal-line {
            flex: 1;
            height: 2px;
            background-color: #000;
            position: relative;
        }

        .circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #000;
            position: absolute;
        }

        .start {
            left: -6px;
        }

        .end {
            right: -6px;
        }
    </style>
    
</head>
<body class="relative">

    <!-- {{-- loading page --}} -->
    <div id="preloaderLoadingPage">
        <div class="sk-three-bounce">
            <div class="centerpreloader">
                <div class="ui-loading"></div>
                <center>
                    <h6 style="color: white;">Loading...</h6>
                </center>
            </div>
        </div>
    </div>
    <!-- {{-- end loading page --}} -->

    <!-- Map container -->
    <div id="map" class="absolute inset-0"></div>

    <!-- Floating Widget -->
    <div class="absolute top-2 left-2 widget">
        <div class="text-left mb-2 bg-white shadow-lg rounded-lg p-3 w-80">
            <img src="https://nginovasi.com/asset/img/Logo%20NGI.svg" style="width: 100%; height: 5vh;">
            <p><center><strong>NGI - Share Bus Position</strong></center></p>
        </div>
        <div class="text-left mb-2 bg-white shadow-lg rounded-lg p-3 w-80">
            <div class="flex items-center space-x-4">
                <!-- Profile Image -->
                <img class="w-10 h-10 rounded-full" src="<?= base_url('public/img/icon_bus.jpeg') ?>">
            
                <!-- Information Section -->
                <div>
                    <p class="font-bold text-gray-800" id="nomor_kendaraan"></p>
                    <p class="text-gray-600" id="po_name"></p>
                </div>
            </div>
            <div class="border-t border-dotted border-gray-400 mt-2 mb-2"></div>
            <div class="flex flex-col space-y-2">
                <div class="flex items-left space-x-2">
                    <div class="bg-blue-500 rounded-full w-8 h-8 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">ASAL TERMINAL</p>
                        <p class="font-bold text-gray-800" id="terminal_asal">Terminal A1 Madura</p>
                    </div>
                </div>
            
                <div class="flex items-center justify-left">
                    <div class="h-6 border-l-2 border-dotted border-gray-400 ml-4"></div>
                </div>
            
                <div class="flex items-left space-x-2">
                    <div class="bg-green-500 rounded-full w-8 h-8 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9h18M9 22V9M15 22V9m0 0l3 3H6l3-3" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">TUJUAN TERMINAL</p>
                        <p class="font-bold text-gray-800" id="terminal_tujuan">Terminal A1 Surabaya</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-dotted border-gray-400 mt-2 mb-2"></div>
            <p class="text-xs text-gray-400 font-semibold">ETA (Estimasi)</p>
            <p class="text-xs text-gray-800 font-medium" id="eta">1 Jam</p>

            <p class="text-xs text-gray-400 font-semibold mt-2">KETERANGAN</p>
            <p class="text-xs text-gray-800 font-medium" id="status_remark">Perjalanan</p>

            <p class="text-xs text-gray-400 font-semibold mt-2">UPDATE</p>
            <p class="text-xs text-gray-800 font-medium" id="date_tracker">02-02-2025 10:10:10</p>

            <!-- {{-- <p class="text-xs text-gray-400 font-semibold mt-2">ESTIMASI PENUMPANG</p> -->
            <!-- <p class="text-xs text-gray-800 font-medium" id="estimasiTotalPenumpang">-</p> --}} -->

            <div class="border-t border-dotted border-gray-400 mt-2 mb-2"></div>

            <div class="flex items-center justify-between">
                <div class="text-left">
                    <p class="text-xs font-bold text-gray-700">KECEPATAN</p>
                    <p class="text-sm text-gray-600" id="speed">50 km/jam</p>
                </div>
            
                <div class="text-right">
                    <p class="text-xs font-bold text-gray-700">JARAK</p>
                    <p class="text-sm text-gray-600" id="jarak">123 km</p>
                </div>
            </div>

            <div class="border-t border-dotted border-gray-400 mt-2 mb-2"></div>
            <div class="flex items-center justify-between">
                <!-- Waktu Section -->
                <div class="text-left">
                    <p class="text-xs font-bold text-gray-700">JADWAL</p>
                    <p class="text-sm text-gray-600" id="waktu">12:12:12</p>
                </div>
            
                <!-- Clock Icon -->
                <div class="flex items-center justify-center">
                    <div class="bg-gray-700 rounded-full p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3M12 2a10 10 0 100 20 10 10 0 000-20z" />
                        </svg>
                    </div>
                </div>
            
                <!-- Estimasi Section -->
                <div class="text-right">
                    <p class="text-xs font-bold text-gray-700">ESTIMASI</p>
                    <p class="text-sm text-gray-600" id="waktu_eta">13:13:13</p>
                </div>
            </div>
            <div class="border-t border-dotted border-gray-400 mt-2 mb-2"></div>
            <p class="text-xs text-gray-400 font-semibold">STATUS PERJALANAN</p>
            <p class="text-xs text-gray-800 font-medium" id="statusPerjalanan" style="font-size: 11px;">Sedang dalam perjalanan</p>
            <div class="border-t border-dotted border-gray-400 mt-2 mb-2"></div>
            <p class="text-xs text-gray-400 font-semibold">RUTE TRAYEK</p>
            <p class="text-xs text-gray-800 font-medium" id="ruteTrayek" style="font-size: 10px;">Gili Anyar - Babatan</p>
            <div class="border-t border-dotted border-gray-400 mt-2 mb-2"></div>
            <p class="text-xs text-gray-400 font-semibold">LOKASI BUS</p>
            <p class="text-xs text-gray-800 font-medium" id="location" style="font-size: 10px;">Gili Anyar</p>
            <!-- {{-- <div class="border-t border-dotted border-gray-400 mt-2 mb-2"></div> --}} -->
            <!-- {{-- <img src="" id="cameraInBusImg"> --}} -->
            <!-- {{-- <div class="border-t border-dotted border-gray-400 mt-2 mb-2"></div>
            <p class="text-xm text-gray-400 font-semibold mb-2">RUTE TRAYEK</p>
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center">
                    <div class="bg-green-500 rounded-full w-4 h-4 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="ml-3 text-sm text-gray-800">TERMINAL TIDAR</p>
                </div>
                <p class="text-sm text-gray-500">11:17</p>
            </div>
            <div class="ml-2 border-l-2 border-dotted border-gray-300 h-4 mb-1"></div>
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center">
                    <div class="bg-green-500 rounded-full w-4 h-4 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="ml-3 text-sm text-gray-800">TERMINAL GIWANGAN</p>
                </div>
                <p class="text-sm text-gray-500">11:17</p>
            </div>

            <div class="ml-2 border-l-2 border-dotted border-gray-300 h-4 mb-1"></div>

            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center">
                    <div class="border-2 border-green-500 rounded-full w-4 h-4 flex items-center justify-center">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    </div>
                    <p class="ml-3 text-sm text-gray-800">TERMINAL MANGKANG</p>
                </div>
                <p class="text-sm text-gray-500">11:22</p>
            </div>

            <div class="ml-2 border-l-2 border-dotted border-gray-300 h-4 mb-1"></div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="bg-gray-300 rounded-full w-4 h-4 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" />
                        </svg>
                    </div>
                    <p class="ml-3 text-sm text-gray-500">TERMINAL MANGKANG</p>
                </div>
            </div> --}} -->

        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="<?= base_url('public/js/leaflet.js') ?>"></script>
    <script src="<?= base_url('public/js/leaflet-rm.js') ?>"></script>
    <script src="//unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script src="<?= base_url('public/js/leaflet-moving-marker.js') ?>"></script>
    <script src="<?= base_url('public/js/leaflet-rotate.js') ?>"></script>
    <script src="https://unpkg.com/@turf/turf/turf.min.js"></script>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.7.5/socket.io.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="<?= base_url('public/js/axios.js') ?>"></script>
    <script src="<?= base_url('public/js/restAPI.js') ?>"></script>
    <script>
        let baseUrlNgiGps;
        let data_api = null;
        let gps_sn = "";
        let socket;
        let latest_routes = null;
        let latest_date_tracker = null;
        let lockedTerminal = null;
        
        var map;
        var busMarker = {};
        var terminalMarker = {};
        var svgImage = {};
    
        var numDeltas = 100;
        var delay = 1;
        var i = 0;
        var theBus_i = {};
        var theBus_deltaLat = {};
        var theBus_deltaLng = {};
        var theBusPos = {};
        let terminal_use = null;
        

        let pin = `<svg version="1.1" xmlns="http://www.w3.org/2000/svg" fill="blue" stroke="white" stroke-width="4" width="258" height="380">
            <path d="M0 0 C1.03125 0.721875 2.0625 1.44375 3.125 2.1875 C11.81371417 9.76349055 15.08322012 21.62468082 15.8828125 32.70703125 C16.13532837 39.80272722 16.07028433 46.90128277 16 54 C16.93070312 54.12246094 17.86140625 54.24492188 18.8203125 54.37109375 C23.05223062 55.20811932 24.48355549 56.52138554 27 60 C27.70969896 62.83307248 27.70969896 65.16692752 27 68 C24.48355549 71.47861446 23.05223062 72.79188068 18.8203125 73.62890625 C17.88960937 73.75136719 16.95890625 73.87382813 16 74 C15.99576806 74.95268642 15.99153612 75.90537285 15.98717594 76.88692856 C15.88384828 100.09917013 15.77508301 123.31138141 15.66066074 146.52357101 C15.60545143 157.74893942 15.55199009 168.97431101 15.50268555 180.19970703 C15.45969264 189.98708208 15.41337744 199.7744349 15.36312145 209.56177545 C15.3366417 214.7410436 15.31179258 219.92030769 15.29092598 225.09960175 C15.27120438 229.98164036 15.24701653 234.86363713 15.21950912 239.74563789 C15.2102202 241.53052821 15.20251645 243.31542764 15.19658089 245.10033226 C15.08402841 277.95442709 15.08402841 277.95442709 8.203125 286.13671875 C-1.79488577 295.37906592 -13.11778569 295.15694873 -25.9765625 295.203125 C-27.2194101 295.20882507 -28.46225769 295.21452515 -29.74276733 295.22039795 C-32.36675168 295.22981655 -34.99074769 295.2363694 -37.61474609 295.24023438 C-41.59098391 295.24677026 -45.56685172 295.28096307 -49.54296875 295.3125 C-52.10416363 295.31903351 -54.66536217 295.32428261 -57.2265625 295.328125 C-58.39788727 295.3404718 -59.56921204 295.3528186 -60.77603149 295.36553955 C-70.88016685 295.3388069 -81.83764412 294.89019102 -90.125 288.5625 C-96.98719868 281.47071103 -98.16410408 273.17936233 -98.57397461 263.62231445 C-98.61353995 262.73622403 -98.6531053 261.85013361 -98.69386959 260.93719196 C-99.313493 245.01360651 -99.2345165 229.06240389 -99.30332303 213.12927794 C-99.32545926 208.22462141 -99.35271037 203.3199931 -99.37937927 198.4153595 C-99.42893807 189.16188218 -99.47401061 179.90838889 -99.51740164 170.65488082 C-99.56709237 160.10560365 -99.62189055 149.55635484 -99.67708123 139.00710523 C-99.7903348 117.33809668 -99.89729311 95.66906094 -100 74 C-101.65708984 73.57267578 -101.65708984 73.57267578 -103.34765625 73.13671875 C-107.59058779 71.81619139 -108.85837294 70.85492871 -111 67 C-111.58827012 62.88210918 -111.4635928 60.80237215 -109.375 57.1875 C-106.67884508 54.70419942 -104.46806123 54.04991991 -101 53 C-100.94392578 51.1128125 -100.94392578 51.1128125 -100.88671875 49.1875 C-99.63126633 13.96220265 -99.63126633 13.96220265 -86.8125 1.625 C-65.12850409 -16.07695994 -22.43971004 -16.37492354 0 0 Z M-88 17 C-91.04641536 20.50799344 -91.22372929 23.17543931 -91.1628418 27.75292969 C-90.88322381 31.61140631 -89.93196445 35.22684643 -88.875 38.9375 C-88.65271729 39.72584229 -88.43043457 40.51418457 -88.20141602 41.32641602 C-85.90616607 49.00982279 -82.82359604 55.43763462 -76 60 C-72.79062745 61.06979085 -70.45380667 61.13967094 -67.07910156 61.16113281 C-65.86063538 61.17074036 -64.64216919 61.1803479 -63.38677979 61.19024658 C-62.06804871 61.19449646 -60.74931763 61.19874634 -59.390625 61.203125 C-58.02162678 61.20888039 -56.65262857 61.21463807 -55.28363037 61.22039795 C-52.41206488 61.23090038 -49.54052731 61.23674751 -46.66894531 61.24023438 C-43.00275732 61.24570751 -39.33694289 61.26971293 -35.67087173 61.29820633 C-32.84144034 61.31690344 -30.01209749 61.32204212 -27.18260956 61.32357025 C-25.17803132 61.32804156 -23.17350063 61.34665625 -21.16900635 61.36553955 C-10.72000712 61.46939038 -10.72000712 61.46939038 -2.12475586 56.1315918 C1.60616497 50.26176292 3.7299622 44.53736066 5.4375 37.8125 C5.70498047 36.85408203 5.97246094 35.89566406 6.24804688 34.90820312 C7.75857889 29.20131063 8.86292544 24.53387001 5.9765625 19.05859375 C-3.16694587 8.09166897 -20.44890788 5.38439448 -34 4 C-52.02320307 3.00614363 -73.67244579 4.96611127 -88 17 Z M-92 56 C-93.53230794 62.61715659 -94.2992519 68.84300574 -94.265625 75.62890625 C-94.26753845 76.50344955 -94.2694519 77.37799286 -94.27142334 78.27903748 C-94.27277318 80.11172781 -94.26916156 81.94442811 -94.26074219 83.77709961 C-94.25001233 86.55930307 -94.26068531 89.34086291 -94.2734375 92.12304688 C-94.27211594 93.91666807 -94.26955363 95.71028887 -94.265625 97.50390625 C-94.26967346 98.32290909 -94.27372192 99.14191193 -94.27789307 99.98573303 C-94.23338626 105.54805254 -93.40851427 110.62169416 -92 116 C-90.68 116.33 -89.36 116.66 -88 117 C-82.34587267 110.56599303 -80.53351334 104.9102773 -80.53125 96.57421875 C-80.52363647 95.78658646 -80.51602295 94.99895416 -80.50817871 94.18745422 C-80.49742991 92.52937977 -80.49450325 90.8712379 -80.49902344 89.21313477 C-80.49999359 86.70406494 -80.4583628 84.19808085 -80.4140625 81.68945312 C-80.34895728 72.84493067 -80.5312022 65.42298995 -86 58 C-86.66 57.34 -87.32 56.68 -88 56 C-89.32 56 -90.64 56 -92 56 Z M4 56 C2.22262351 58.09996862 2.22262351 58.09996862 0.8125 60.8125 C0.30332031 61.70582031 -0.20585937 62.59914063 -0.73046875 63.51953125 C-3.94716966 69.80446995 -3.33560609 76.62260944 -3.4050293 83.52075195 C-3.42646292 85.11602622 -3.46038922 86.71118769 -3.50756836 88.3059082 C-3.79879649 98.2584483 -3.87483799 107.96133739 3 116 C5.6040841 116.87905575 5.6040841 116.87905575 8 117 C10.11725729 110.94100676 10.29160513 105.41247725 10.265625 99.02734375 C10.26753845 97.98606827 10.2694519 96.94479279 10.27142334 95.8719635 C10.27277947 93.67920692 10.26912312 91.48644216 10.26074219 89.29370117 C10.2500239 85.94496589 10.26066269 82.59676571 10.2734375 79.24804688 C10.27211602 77.11197799 10.26955385 74.97590943 10.265625 72.83984375 C10.26967346 71.84282944 10.27372192 70.84581512 10.27789307 69.81858826 C10.24425009 64.81322872 9.98537196 60.68888864 8 56 C6.68 56 5.36 56 4 56 Z M-60 104 C-60.43321985 106.48505243 -60.43321985 106.48505243 -60.328125 109.375 C-60.32296875 110.44234375 -60.3178125 111.5096875 -60.3125 112.609375 C-60.291875 113.72828125 -60.27125 114.8471875 -60.25 116 C-60.24484375 117.11890625 -60.2396875 118.2378125 -60.234375 119.390625 C-60.43210268 124.86709976 -60.43210268 124.86709976 -59 130 C-54.17619198 130.0246519 -49.35242033 130.04283693 -44.52856445 130.05493164 C-42.88630536 130.05997174 -41.24405077 130.06680378 -39.60180664 130.07543945 C-37.24624521 130.08751926 -34.89074291 130.09323139 -32.53515625 130.09765625 C-31.42733543 130.10539818 -31.42733543 130.10539818 -30.2971344 130.11329651 C-28.53101912 130.1134925 -26.76502854 130.06194719 -25 130 C-22.39904644 127.39904644 -23.76535464 120.06143851 -23.75 116.5625 C-23.729375 115.39138672 -23.70875 114.22027344 -23.6875 113.01367188 C-23.68234375 111.89669922 -23.6771875 110.77972656 -23.671875 109.62890625 C-23.6625293 108.59838135 -23.65318359 107.56785645 -23.64355469 106.50610352 C-23.76118164 105.67908936 -23.87880859 104.8520752 -24 104 C-27.6429048 101.5713968 -29.41303841 101.74238099 -33.75 101.734375 C-34.73178223 101.73150482 -34.73178223 101.73150482 -35.73339844 101.72857666 C-37.11523829 101.72721755 -38.49709103 101.73089487 -39.87890625 101.73925781 C-41.9924796 101.74996191 -44.10520305 101.73935399 -46.21875 101.7265625 C-47.56250187 101.72788414 -48.90625322 101.73044664 -50.25 101.734375 C-51.47203125 101.73663086 -52.6940625 101.73888672 -53.953125 101.74121094 C-57.17472243 101.76815909 -57.17472243 101.76815909 -60 104 Z M-92 126 C-93.55969767 132.70292026 -94.29867945 139.00822639 -94.265625 145.8828125 C-94.26753845 146.78192352 -94.2694519 147.68103455 -94.27142334 148.60739136 C-94.27277346 150.49135623 -94.26915989 152.37533079 -94.26074219 154.25927734 C-94.25000042 157.12488846 -94.26069064 159.98987642 -94.2734375 162.85546875 C-94.27211619 164.69791803 -94.26955438 166.54036693 -94.265625 168.3828125 C-94.26967346 169.2288504 -94.27372192 170.07488831 -94.27789307 170.94656372 C-94.23626151 176.28225315 -93.48411327 180.8704506 -92 186 C-90.68 186.33 -89.36 186.66 -88 187 C-87.0165504 185.56542672 -86.03839079 184.12722594 -85.0625 182.6875 C-84.24458984 181.48673828 -84.24458984 181.48673828 -83.41015625 180.26171875 C-79.63001961 174.19884031 -80.69882989 166.44078487 -80.64990234 159.51489258 C-80.63342052 157.80902388 -80.60623416 156.10322199 -80.56787109 154.39770508 C-80.14900818 140.43575873 -80.14900818 140.43575873 -86 128 C-86.66 127.34 -87.32 126.68 -88 126 C-89.32 126 -90.64 126 -92 126 Z M2.5859375 127.3203125 C2.18632812 127.91585937 1.78671875 128.51140625 1.375 129.125 C0.97023438 129.70507812 0.56546875 130.28515625 0.1484375 130.8828125 C-2.65353036 136.04834509 -3.33555345 141.02327926 -3.40625 146.8203125 C-3.41765015 147.5602243 -3.42905029 148.30013611 -3.4407959 149.06246948 C-3.45960763 150.62394578 -3.47272862 152.18549989 -3.48046875 153.74707031 C-3.49978213 156.09847387 -3.56184428 158.44629791 -3.625 160.796875 C-3.75505132 169.96816185 -3.26505188 178.64216662 3 186 C5.60383033 186.88005048 5.60383033 186.88005048 8 187 C10.46577484 181.25611586 10.28934509 175.78696643 10.265625 169.65234375 C10.26753845 168.56288956 10.2694519 167.47343536 10.27142334 166.35096741 C10.2727811 164.05388151 10.26911332 161.75678784 10.26074219 159.4597168 C10.25002677 155.94627655 10.26065694 152.4333464 10.2734375 148.91992188 C10.27211604 146.68489471 10.26955391 144.44986786 10.265625 142.21484375 C10.26967346 141.16578354 10.27372192 140.11672333 10.27789307 139.03587341 C10.27158875 138.05457962 10.26528442 137.07328583 10.25878906 136.06225586 C10.25719788 135.20321152 10.25560669 134.34416718 10.25396729 133.45909119 C9.96776953 130.6879218 9.13984751 128.52990071 8 126 C4.16484211 125.54502526 4.16484211 125.54502526 2.5859375 127.3203125 Z M-44.5625 192.4375 C-46.4487207 192.49647461 -46.4487207 192.49647461 -48.37304688 192.55664062 C-49.57123047 192.59853516 -50.76941406 192.64042969 -52.00390625 192.68359375 C-53.10549072 192.72025146 -54.2070752 192.75690918 -55.34204102 192.79467773 C-57.97416545 192.83000037 -57.97416545 192.83000037 -60 194 C-60 202.25 -60 210.5 -60 219 C-55.88197155 221.05901422 -54.10622901 221.25665229 -49.625 221.265625 C-48.39265625 221.26820313 -47.1603125 221.27078125 -45.890625 221.2734375 C-44.60671875 221.26570313 -43.3228125 221.25796875 -42 221.25 C-40.07414062 221.26160156 -40.07414062 221.26160156 -38.109375 221.2734375 C-36.26085937 221.26957031 -36.26085937 221.26957031 -34.375 221.265625 C-33.24835938 221.26336914 -32.12171875 221.26111328 -30.9609375 221.25878906 C-27.70054784 221.15663909 -27.70054784 221.15663909 -24 219 C-24 210.75 -24 202.5 -24 194 C-30.37541032 190.81229484 -37.5262218 192.19511921 -44.5625 192.4375 Z M-90 195 C-92.14646991 195.78616307 -92.14646991 195.78616307 -92.62347412 197.72363281 C-93.8710157 203.45934067 -94.29373904 208.88885136 -94.265625 214.75 C-94.26849518 216.09723145 -94.26849518 216.09723145 -94.27142334 217.47167969 C-94.27277522 219.35742475 -94.26914906 221.24317944 -94.26074219 223.12890625 C-94.25002842 225.99240298 -94.26067165 228.85527246 -94.2734375 231.71875 C-94.27211573 233.56250137 -94.26955297 235.40625235 -94.265625 237.25 C-94.26967346 238.09401367 -94.27372192 238.93802734 -94.27789307 239.80761719 C-94.23416233 245.43421199 -93.43235676 250.56051544 -92 256 C-90.68 256.33 -89.36 256.66 -88 257 C-87.0165504 255.56542672 -86.03839079 254.12722594 -85.0625 252.6875 C-84.24458984 251.48673828 -84.24458984 251.48673828 -83.41015625 250.26171875 C-79.58172715 244.12138509 -80.65964348 236.161208 -80.5949707 229.14257812 C-80.57349707 227.39497996 -80.53952184 225.6474862 -80.49243164 223.90039062 C-80.01998528 208.93820116 -80.01998528 208.93820116 -87 196 C-87.99 195.67 -88.98 195.34 -90 195 Z M1.9375 198 C-1.98215264 204.06913958 -3.46664623 209.84844219 -3.46875 217.0859375 C-3.47636353 217.82776276 -3.48397705 218.56958801 -3.49182129 219.33389282 C-3.5025489 220.8918321 -3.50550554 222.44984287 -3.50097656 224.0078125 C-3.50000486 226.3632263 -3.54169011 228.71536791 -3.5859375 231.0703125 C-3.65353962 239.7080824 -3.01315649 246.66315693 2 254 C2.66 254.99 3.32 255.98 4 257 C5.32 257 6.64 257 8 257 C10.11725729 250.94100676 10.29160513 245.41247725 10.265625 239.02734375 C10.26753845 237.98606827 10.2694519 236.94479279 10.27142334 235.8719635 C10.27277947 233.67920692 10.26912312 231.48644216 10.26074219 229.29370117 C10.2500239 225.94496589 10.26066269 222.59676571 10.2734375 219.24804688 C10.27211602 217.11197799 10.26955385 214.97590943 10.265625 212.83984375 C10.26967346 211.84282944 10.27372192 210.84581512 10.27789307 209.81858826 C10.24425009 204.81322872 9.98537196 200.68888864 8 196 C3.99977339 195.53590278 3.99977339 195.53590278 1.9375 198 Z M-85.64453125 260.7265625 C-88.5122767 265.38784774 -90.93748402 270.39616295 -90 276 C-84.68471229 282.84468654 -74.81794762 283.90165095 -66.67041016 284.98901367 C-45.19053507 287.43125856 -12.60501427 290.79045523 5.8125 276.875 C7.45979657 272.88680832 6.61170977 270.49887694 5.19921875 266.51171875 C3.15497483 262.23012644 0.31999944 258.15999972 -4 256 C-6.306383 255.89236848 -8.61594388 255.85106853 -10.92480469 255.83886719 C-11.63722321 255.83390228 -12.34964172 255.82893738 -13.08364868 255.82382202 C-15.44640055 255.80918834 -17.80908521 255.80239538 -20.171875 255.796875 C-21.81374107 255.79112323 -23.45560712 255.7853655 -25.09747314 255.77960205 C-28.54415195 255.76908774 -31.99080741 255.76324833 -35.4375 255.75976562 C-39.84701249 255.75428898 -44.25621492 255.73028071 -48.66563034 255.70179367 C-52.05947763 255.68313573 -55.45325084 255.67795995 -58.84714508 255.67642975 C-60.47219787 255.67340831 -62.09724952 255.66539696 -63.72225189 255.65224457 C-66.00094381 255.63512248 -68.27888641 255.6370793 -70.55761719 255.64355469 C-71.85286316 255.63990906 -73.14810913 255.63626343 -74.48260498 255.63250732 C-79.00242541 256.10473205 -82.47703775 257.40107051 -85.64453125 260.7265625 Z " transform="translate(151,38)"/>
        </svg>`;

        function loadingPage(show) {
            if (show == true) {
                document.getElementById('preloaderLoadingPage').style.display = '';
            } else {
                document.getElementById('preloaderLoadingPage').style.display = 'none';
            }
            return;
        }

        function svgStringToImageSrc(svgString) {
            return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgString)
        }
    
        var loadSVG = function(item) {
            var newPath = $($(pin)[0].children[0])
                .attr('fill', '#1167b1')
                .attr('stroke', 'dark')
                .attr('stroke-width', '4');
    
            var newSVG = `<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="216" height="360">` + newPath[0]
                .outerHTML + `</svg>`;
    
            return L.divIcon({
                html: `<img src="${svgStringToImageSrc(newSVG)}" width="50" height="56" style="width:50px;">`,
                className: '',
                iconSize: [50, 56],
                popupAnchor: [-10, -18]
            });
        }

        var newMarker = function(item) {
            if (typeof parseInt(item.angle) == 'number') {
                var icon = loadSVG(item);
                busMarker[item.gps_sn] = L.marker([item.latitude, item.longitude], {
                        icon: icon,
                        rotationAngle: parseInt(item.angle)
                    })
                    .addTo(map);
            }
        }

        var updateMarker = function(item) {
            let angle = typeof parseInt(item.angle) === 'number' ? parseInt(item.angle) % 360 : 0;
            var iconBus = loadSVG(item);
            let optionsBus = {
                icon: iconBus,
                rotationAngle: angle,
                rotationOrigin: 'center center'
            };
            let routesData = item.routes;
            if (latest_routes != null) {
                routesData.forEach(element => {
                    latest_routes.push(element)
                });
                routesData = latest_routes;
            }
            let durationMove = 4500;
            optionsBus.autostart = false;
            var routeCoordinates = routesData.map(point => [point[0], point[1]]);
            latest_routes = item.latest_lat_lng
            latest_date_tracker = item.date_tracker;
            map.removeLayer(busMarker[item.gps_sn]);
            busMarker[item.gps_sn] = L.Marker.movingMarker(routeCoordinates, durationMove, optionsBus);
            busMarker[item.gps_sn].addTo(map);
            map.flyTo(busMarker[item.gps_sn].getLatLng(), 15);
            map.once('zoomend', function() {

                busMarker[item.gps_sn].start();

                busMarker[item.gps_sn].on('move', function(e) {
                    var latLng = busMarker[item.gps_sn].getLatLng();
                    map.panTo(latLng);

                    var index = routeCoordinates.findIndex(function(pos) {
                        return pos[0] === latLng.lat && pos[1] === latLng.lng;
                    });

                    if (index !== -1 && routesData[index][2] !== undefined) {
                        var angle = routesData[index][2] % 360;
                        if (angle < 0) angle += 360;
                        busMarker[item.gps_sn].setRotationAngle(angle); 
                    }
                });
            });
        }

        async function setMapRuteTrayek(data) {

            let routes = data.rute_point;

            let waypoints = routes.map(coord => L.latLng(parseFloat(coord.latitude), parseFloat(coord.longitude)));

            var customIcon = L.icon({
                iconUrl: '<?= base_url("public/img/icon_terminal.png") ?>',
                iconSize: [35, 35],
                iconAnchor: [17, 36],
                popupAnchor: [0, -33]
            });

            routes.forEach((coord) => {
                if (terminalMarker[coord.id]) {
                    map.removeLayer(terminalMarker[coord.id]);
                }

                terminalMarker[coord.id] = L.marker(
                    [parseFloat(coord.latitude), parseFloat(coord.longitude)], 
                    { icon: customIcon }
                ).addTo(map);

                let popup = L.popup({
                        autoClose: false,
                        closeOnClick: false
                    })
                    .setContent(`${coord.nama}`)
                    .setLatLng([coord.latitude, coord.longitude]);
                
                terminalMarker[coord.id].bindPopup(popup).openPopup();

                L.circle(
                    [parseFloat(coord.latitude), parseFloat(coord.longitude)],
                    {
                        color: 'blue', // Ganti dengan warna yang diinginkan
                        fillColor: 'blue', // Ganti dengan warna isi yang diinginkan
                        fillOpacity: 0.5, // Atur opasitas warna isi
                        radius: 100 // Radius dalam meter
                    }
                ).addTo(map);
            });

            L.Routing.control({
                waypoints: waypoints,
                createMarker: function(i, waypoint, n) {
                    let terminalMarker = L.marker(waypoint.latLng, { icon: customIcon });
                    terminalMarker.bindPopup(`${routes[i].nama}`, {
                        autoClose: false,
                        closeOnClick: false
                    }).openPopup();
                    return terminalMarker;
                },
                routeWhileDragging: false,
                fitSelectedRoutes: true,
                showAlternatives: false,
                addWaypoints: false,
                lineOptions: {
                    styles: [{
                        color: 'blue',
                        weight: 3
                    }]
                }
            }).addTo(map);
            
            $('.leaflet-routing-container.leaflet-bar.leaflet-control').hide();
            map.fitBounds(waypoints);
        }


        function removeCompanyPrefixes(text) {
            const regex = /^(PT\.|CV\.|CV)\s*/i;
            return text.replace(regex, '').trim();
        }

        document.addEventListener('DOMContentLoaded', function() {

            map = L.map('map', {
                center: [-7.257241, 112.73726],
                zoom: 11,
                minZoom: 8
            });

            let layerOsm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18
            });

            layerOsm.addTo(map);
        });

        function setTerminalMarker(data) {
                var firstTerminal = [data.data_rute_asal.latitude, data.data_rute_asal.longitude];
                var nextTerminal = [data.data_rute_tujuan.latitude, data.data_rute_tujuan.longitude];
    
                L.marker(firstTerminal).addTo(map).bindPopup(data.data_rute_asal.nama).openPopup();
                L.marker(nextTerminal).addTo(map).bindPopup(data.data_rute_tujuan.nama).openPopup();
    
                var latlngs = [firstTerminal, nextTerminal];
                var polyline = L.polyline(latlngs, {color: 'black', weight: 2, dashArray: '5, 10'}).addTo(map);

                var busIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: "<i class='fas fa-bus' style='transform: rotate(-45deg); font-size: 24px;'></i>",
                    iconSize: [30, 42],
                    iconAnchor: [15, 21]
                });
        }

        function isDateGreater(date1, date2) {
            const d1 = new Date(date1.replace(' ', 'T'));
            const d2 = new Date(date2.replace(' ', 'T'));
            return d1 > d2;
        }

        async function setDetailData(data, isSocket = false) {
            if (data && data.latitude && data.longitude) {
                if (isSocket == false) {
                    theBusPos[data.gps_sn] = [data.latitude, data.longitude];
                    await newMarker(data);
                } else {
                    await updateMarker(data);
                }
            } else {
                console.error('Invalid data provided:', data);
            }
        }

        async function getData(gps_sn, loading = false) {
            if(loading) {
                loadingPage(true);
            }
            let getDataRest = await CallAPI(
            'GET',"{{ route('information.track.bus.data') ?>", {
                gps_sn: gps_sn
            }
            ).then(function(response) {
                return response;
            }).catch(function(error) {
                loadingPage(false)
                let resp = error.response;
                return resp;
            })
            if(loading) {
                loadingPage(false);
            }
            if (getDataRest.status == 200) {
                let data = getDataRest.data.data;
                if (data_api!=null) {
                    data_api = data;
                }
                
                if(loading) {
                    setDetailData(data, false);
                }

                if(terminal_use == null) {
                    terminal_use = data.data_rute_asal.id;
                    setMapRuteTrayek(data);
                } else {
                    if(terminal_use != data.data_rute_asal.id) {
                        terminal_use = data.data_rute_asal.id;
                        setMapRuteTrayek(data);
                    }
                }

                if(loading) {
                    latest_date_tracker = data.date_tracker;
                    latest_routes = [[parseFloat(data.latitude), parseFloat(data.longitude), parseInt(data.angle)]];
                    if(data.data_rute_tujuan.eta.km_validator <= 5) {
                        setTimeout(() => {
                            map.flyTo(busMarker[data.gps_sn].getLatLng(), 15);
                        }, 1500);
                    }

                }

                let po_name = data.data_rute_asal.po_name == "" ? "-" : removeCompanyPrefixes(data.data_rute_asal.po_name);
                const parsedDate = moment(data.date_tracker, 'YYYY-MM-DD HH:mm:ss');
                const updated_at_date = parsedDate.format('D MMM, YYYY');
                const updated_at_time = parsedDate.format('HH:mm:ss A');
                let status = data.data_rute_tujuan.status_remark == "TELAH TIBA" ? true : false;

                if(status) {
                    $("#location").html(data.address);
                    $("#eta").html("-");
                    $("#speed").html(""+data.speed+" KM/H");
                    $("#jarak").html(""+data.data_rute_tujuan.eta.km_validator+" KM");
                } else {
                    $("#location").html(data.address);
                    $("#eta").html(data.data_rute_tujuan.eta.time);
                    $("#speed").html(""+data.speed+" KM/H");
                    $("#jarak").html(""+data.data_rute_tujuan.eta.km_validator+" KM");
                }
                if(!status) {
                    $("#status_remark").html(data.data_rute_tujuan.status_remark);
                } else {
                    $("#status_remark").html(data.data_rute_tujuan.status_remark);
                }
                $("#nomor_kendaraan").html(data.nomor_kendaraan);
                $("#po_name").html(po_name);
                $("#terminal_asal").html(data.data_rute_asal.nama.replace("TERMINAL ",""));
                $("#terminal_tujuan").html(data.data_rute_tujuan.nama.replace("TERMINAL ",""));
                $("#waktu").html(data.data_rute_asal.eta.eta_time);
                $("#waktu_eta").html(data.data_rute_tujuan.eta.eta_time);
                $("#date_tracker").html(""+updated_at_date+" "+updated_at_time+" ("+data.timezone+")");
                $("#ruteTrayek").html(data.rute_trayek);
                $("#statusPerjalanan").html(data.data_rute_tujuan.jenis_perjalanan.toUpperCase());
            }
        }

        async function initPageLoad() {
            setTimeout(() => {
                loadingPage(false);
            }, 1000);
            // await getData(gps_sn,true);

            baseUrlNgiGps = `https://gps.brtnusantara.com:8448`;
            socket = io(baseUrlNgiGps,{secure:true,transports : ['websocket']});
            socket.on("connection", (socket) => {
                console.info(socket.handshake.headers);
            });
            socket.on("disconnect", () => {
              console.info("Koneksi ke server Socket.IO terputus");
            });

            // socket = io('{{ env('SOCKET_BASE_URL') }}', {
            //     withCredentials: true,
            //     secure: true,
            //     transports: ['websocket']
            // });

            // socket.on("passenger-0000000000", async function(message) {
            //     $("#estimasiTotalPenumpang").html(message.total_passengers);
            //     $("#cameraInBusImg").attr('src', message.img);
            // });

            $.ajax({
                method: 'get',
                url: '<?= base_url('polyline') ?>',
                // data: JSON.stringify({ key:'ngiraya', plat:'L ',pref:'5' }),
                contentType: 'application/json',
                success:function(response){
                    var ret = response;
                    console.log("🚀 ~ ret:", ret)
                    if(ret.status_code==200){
                        let osrmResponse = ret.data;
                        
                        // Extract and decode all geometries
                        var allPoints = [];
                        var startPoints = [];
                        var endPoints = [];
                        
                        osrmResponse.routes[0].legs[0].steps.forEach(step => {
                            var decodedPoints = decodePolyline(step.geometry);
                            allPoints = allPoints.concat(decodedPoints);
                        
                            // Collect start and end points of each step
                            startPoints.push(decodedPoints[0]);
                            endPoints.push(decodedPoints[decodedPoints.length - 1]);
                        });
                        
                        // Add marker at the start of the route
                        L.marker([startPoints[0][0], startPoints[0][1]], {
                            icon: L.icon({
                                iconUrl: 'https://unpkg.com/leaflet/dist/images/marker-icon.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34],
                                shadowUrl: 'https://unpkg.com/leaflet/dist/images/marker-shadow.png',
                                shadowSize: [41, 41]
                            })
                        }).addTo(map).bindPopup('Terminal A1 Madura').openPopup();
                        
                        // Add marker at the end of the route
                        L.marker([endPoints[endPoints.length - 1][0], endPoints[endPoints.length - 1][1]], {
                            icon: L.icon({
                                iconUrl: 'https://unpkg.com/leaflet/dist/images/marker-icon.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34],
                                shadowUrl: 'https://unpkg.com/leaflet/dist/images/marker-shadow.png',
                                shadowSize: [41, 41]
                            })
                        }).addTo(map).bindPopup('Terminal A1 Surabaya').openPopup();
                        
                        // Create a polyline from the decoded points
                        var polyline = L.polyline(allPoints, { color: 'blue', weight: 5 }).addTo(map);
                        
                        // Add another bus marker at the specified coordinates
                        var additionalBusMarker = L.marker([-7.185863819098109, 112.78003666555523], {
                            icon: L.icon({
                                iconUrl: '<?= base_url('public/img/bus-md.svg') ?>',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34],
                                // shadowUrl: 'https://unpkg.com/leaflet/dist/images/marker-shadow.png',
                                // shadowSize: [41, 41]
                            })
                        }).addTo(map).bindPopup('BUS L 123 SMR').openPopup();
                
                        

                        // Fit the map to the polyline bounds
                        map.fitBounds(polyline.getBounds());
                        

                        //     $.each(ret.data,function(index,item){
                        //       theBusPos[item.imei] = [item.lat,item.lng];
                        //       newMarker(item);
                        //     })
                    }
                    //   socket.on('new message jtm', function(ret){
                    //       updateMarker(ret);
                    //   });
                } 
            });

            socket.on('new message jtm', function(ret){
                console.log("🚀 ~ socket.on ~ ret 1:", ret)
            });

            // socket.on("{{ $data['route_type'] }}-{{ $data['gps_sn'] ?>", async function(message) {
            //     if (isDateGreater(message.date_tracker, latest_date_tracker)) {
            //         const currentLocation = {
            //             latitude: parseFloat(message.latitude),
            //             longitude: parseFloat(message.longitude),
            //             angle: parseInt(message.angle)
            //         };
                    
            //         setDetailData(message, true);
            //         getData(message.gps_sn, false);
            //     }
            // });

        }
        

// Function to decode polyline
function decodePolyline(encoded) {
    var points = [];
    var index = 0, lat = 0, lng = 0;

    while (index < encoded.length) {
        var b, shift = 0, result = 0;
        do {
            b = encoded.charCodeAt(index++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);
        var dlat = ((result & 1) ? ~(result >> 1) : (result >> 1));
        lat += dlat;

        shift = 0;
        result = 0;
        do {
            b = encoded.charCodeAt(index++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);
        var dlng = ((result & 1) ? ~(result >> 1) : (result >> 1));
        lng += dlng;

        points.push([lat / 1e5, lng / 1e5]);
    }

    return points;
}

        initPageLoad();
        

    </script>

</body>
</html>
