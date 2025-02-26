<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaflet Map with OSRM and Bus Marker</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.7.5/socket.io.js"></script>
    <script src="<?= base_url('public/js/axios.js') ?>"></script>
    <script src="<?= base_url('public/js/restAPI.js') ?>"></script>

    <style>
        #map {
            height: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div id="map"></div>

    <script>
        let baseUrlNgiGps;
        let socket;
        let vehicleMarker;
        const polylines = []; // Array to store polylines for routes
        const busSpeed = 80; // Speed of the bus in km/h
        let terminals; 
        let busMarker;
        let latest_routes;

        // const terminals = [
        //     { name: 'Terminal Depok 1', coords: [-6.39312335421226, 106.81839435613561], status: "pergi", urutan: 1 },
        //     { name: 'Terminal Jakarta 2', coords: [-6.2130897, 106.9435911], status: "pergi", urutan: 2 },
        //     { name: 'Terminal Tangerang 3', coords: [-6.183665518935763, 106.61259450220149], status: "pulang", urutan: 3 },
        //     { name: 'Terminal Depok 1', coords: [-6.39312335421226, 106.81839435613561], status: "pulang", urutan: 4 },
        // ];

        const map = L.map('map').setView([-6.2088, 106.8456], 10); // Center map in Jakarta

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        // Posisi marker bus
        // let busPosition = [-6.304003912884807, 106.94290973637666];
        // const busMarker = L.marker(busPosition, { icon: L.icon({ iconUrl: 'https://weijun-lab.github.io/Leaflet.TrackPlayer/lib/assets/car.png', iconSize: [20, 34] }) }).addTo(map);

        // Menampilkan terminal pada peta dan menggambar rute antar terminal
        // for (let i = 0; i < terminals.length; i++) {
        //     const terminal = terminals[i];
        //     L.marker(terminal.coords).addTo(map)
        //         .bindPopup(terminal.name);
            
        //     if (i < terminals.length - 1) {
        //         getRoute(terminal.coords, terminals[i + 1].coords, terminal, terminals[i + 1]);
        //     }
        // }

        // Fungsi untuk menggambar rute antara dua titik dengan warna berdasarkan status
        function getRoute(start, end, terminal, nextTerminal) {
            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${start[1]},${start[0]};${end[1]},${end[0]}?overview=full&geometries=geojson`;

            const xhr = new XMLHttpRequest();
            xhr.open('GET', osrmUrl, true);
            xhr.responseType = 'json';

            xhr.onload = function () {
                if (xhr.status === 200) {
                    const data = xhr.response;
                    if (data.routes && data.routes.length > 0) {
                        const route = data.routes[0];
                        const latLngs = route.geometry.coordinates.map(coord => [coord[1], coord[0]]); // Konversi ke [lat, lon]
                        const color = terminal.jenis_perjalanan === "pergi" ? 'blue' : 'red';
                        console.log(`Menuju ${nextTerminal.nama} (${nextTerminal.jenis_perjalanan}) - ETA: ${Math.round(route.distance / 1000 / (busSpeed / 60))} menit`)
                        const polyline = L.polyline(latLngs, { color: color })
                            .addTo(map)
                            .bindPopup(`Menuju ${nextTerminal.nama} (${nextTerminal.jenis_perjalanan}) - ETA: ${Math.round(route.distance / 1000 / (busSpeed / 60))} menit`); // Menghitung ETA dari jarak
                        polylines.push(polyline);
                    }
                }
            };

            xhr.send();
        }

        var updateMarker = function(item) {
            // console.log("🚀 ~ updateMarker ~ item:", item)
            let angle = typeof parseInt(item.angle) === 'number' ? parseInt(item.angle) % 360 : 0;
            let optionsBus = {
                icon: L.icon({ iconUrl: 'https://weijun-lab.github.io/Leaflet.TrackPlayer/lib/assets/car.png', iconSize: [20, 34] }),
                rotationAngle: angle,
                rotationOrigin: 'center center'
            };
            let routesData = item.routes;
            // if (latest_routes != null) {
            //     routesData.forEach(element => {
            //         latest_routes.push(element)
            //     });
            //     routesData = latest_routes;
            // }
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

        // Update direction and information of bus
        function updateBusStatus(busPosition=[]) {
            // Find nearest terminal or destination
            let destination = "Tidak diketahui";
            let distanceToDestination = Infinity;

            terminals.forEach((terminal) => {
                const distance = map.distance(busPosition, terminal.coords);
                console.log(distance)
                if (distance < distanceToDestination) {
                    distanceToDestination = distance;
                    destination = terminal.nama;
                }
            });

            // Calculate ETA based on distance to destination
            const etaInMinutes = Math.round(distanceToDestination / 1000 / (busSpeed / 60)); // Jarak ke terminal dalam kilometer dibagi dengan kecepatan
            const status = (distanceToDestination < 500) ? "sudah sampai" : "sedang menuju";

            busMarker = L.marker(busPosition, { icon: L.icon({ iconUrl: 'https://weijun-lab.github.io/Leaflet.TrackPlayer/lib/assets/car.png', iconSize: [20, 34] }) }).addTo(map);
    
            // Update popup with direction and status
            busMarker.bindPopup(`
                Bus sedang ${status} ke ${destination}.<br>
                Koordinat Bus: ${busPosition[0]}, ${busPosition[1]}<br>
                ETA: ${etaInMinutes} menit
            `).openPopup();

        }

        // Update bus information
        // updateBusStatus();


        // 
        let data_terminals = []

        async function getData(gps_sn="{{ $data['gps_sn'] }}", loading = true) {
            if(loading) {
                // loadingPage(true);
            }
            let getDataRest = await CallAPI(
            'GET',"{{ route('information.track.bus.data') }}", {
                gps_sn: gps_sn
            }
            ).then(function(response) {
                return response;
            }).catch(function(error) {
                // loadingPage(false)
                let resp = error.response;
                return resp;
            })
            if(loading) {
                // loadingPage(false);
            }
            if (getDataRest.status == 200) {
                let data = getDataRest.data;

                if (loading==true) {
                    terminals = data.terminal;
                    terminals.forEach(el => {
                        // data_terminals[] = [el.latitude, el.longitude];
                        el.coords = [el.latitude, el.longitude];
                    });
                    for (let index = 0; index < terminals.length; index++) {
                        console.log(terminals[index].nama, [terminals[index].latitude, terminals[index].longitude]);
                        data_terminals.push([terminals[index].latitude, terminals[index].longitude]);
                        
                    }
                    for (let i = 0; i < terminals.length; i++) {
                        const terminal = terminals[i];
                        // console.log("🚀 ~ getData ~ terminal:", terminal)
                        L.marker(terminal.coords).addTo(map)
                            .bindPopup(terminal.nama);
                        
                        if (i < terminals.length - 1) {
                            getRoute(terminal.coords, terminals[i + 1].coords, terminal, terminals[i + 1]);
                        }
                    }
                }

                let dataBus = data.data;
                console.log("🚀 ~ getData ~ data:", data)
                updateBusStatus([dataBus.latitude, dataBus.longitude])
            }
        }
        

        function isDateGreater(date1, date2) {
            const d1 = new Date(date1.replace(' ', 'T'));
            const d2 = new Date(date2.replace(' ', 'T'));
            return d1 > d2;
        }

        function initPage() {
            // getData();

            baseUrlNgiGps = `<?= getenv('prop.BASE_URL_NGI_GPS') ?>:8448`;
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

            // socket.on("{{ $data['route_type'] }}-{{ $data['gps_sn'] }}", async function(message) {
            //     console.log(message)
            //     if (isDateGreater(message.date_tracker, latest_date_tracker)) {
            //         // getData(message.gps_sn, false);
            //     }
            // });
        }
        initPage();
    </script>
</body>
</html>
