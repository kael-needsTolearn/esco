
@extends('layouts.admin')
@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb" >
            <ol class="breadcrumb bg-light-lighten p-2" style="margin-bottom:0rem;">
                <li class="breadcrumb-item" style="font-weight:600;"><a href="{{route('uhooDashboard')}}"><i class=""></i>Room Wellness</a></li>
                <li class="breadcrumb-item active" style="font-weight:600;" aria-current="page">Wellness</li>
            </ol>
        </nav>
        {{-- <div class="page-title-box">
            <h4 class="page-title">Wellness</h4>
        </div> --}}
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="list-group">

                        <li id="device2" class="list-group-item-mod">
                          <img style="width: 20px;" class="card-img-top mt-2" src="/Modules/assets/images/uHoo-Maaster-Logo.png" alt="Card image cap">&nbsp;&nbsp;
                            <span style="position:absolute;top:13px;">{{$Device_Name}}</span>
                          {{-- <div class="btn-group float-end d-none d-sm-block" >
                            <button type="button" class="btn btn-light">Hour</button>
                            <button type="button" class="btn btn-light">Day</button>
                        </div>        
                        <div class="float-end d-none d-sm-block">
                            <div class="position-relative" id="datepicker2">
                                <span class="dripicons-calendar" style="position: absolute; left:7.5em;top:6px;cursor:default;color:black;"></span>
                                <input type="text" class="form-control" data-provide="datepicker" data-date-format="d-M-yyyy" data-date-container="#datepicker2" style="color: black;width:80%">
                            </div>
                        </div>      --}}
                        </li>
                    </ul>   
                    <div class="row mt-2 mb-1" style="margin-left:2px;">
                        <div class="col-12">
                            <ul class="list-group flex-wrap position-relative list-group-horizontal" style="padding:0.15rem .35rem;">
                                <li id="VI" type="button"class="list-group-item-uhoo text-nowrap text-center d-flex flex-column align-items-center justify-content-center ">
                                    Virus Index <span id="Text-1" class="roboto-slab-header" ><span id="VIV"></span>/10</span>
                                </li>
                                <li id="MI" type="button" class="list-group-item-uhoo  flex-column text-center flex-fill text-nowrap d-flex align-items-center justify-content-center">
                                    Mold Index <span id="Text-2" class="roboto-slab-header" ><span id="MIV"></span>/10</span>
                                </li>
                                <li id="TE" type="button" class="list-group-item-uhoo flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    Temperature <span id="Text-3" class="roboto-slab-header" ><span id="TEV"></span> °C</span>
                                </li>
                                <li id="HU" type="button" class="list-group-item-uhoo flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    Humidity <span id="Text-4" class="roboto-slab-header" ><span id="HUV"></span> °C</span>
                                </li>
                                <li id="CD" type="button" class="list-group-item-uhoo flex-column text-center  flex-fill d-flex align-items-center justify-content-center">
                                    Carbon Dioxide <span id="Text-7" class="roboto-slab-header" ><span id="CDV"></span> ppm</span>
                                </li>
                                <li  id="TV" type="button" class="list-group-item-uhoo flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    TVOC <span id="Text-6" class="roboto-slab-header"  ><span id="TVV"></span> ppb</span>
                                </li>
                                <li id="FO" type="button" class="list-group-item-uhoo flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    Formaldehyde <span id="Text-15" class="roboto-slab-header"  ><span id="FOV"></span> ppb</span>
                                </li>
                                <li id="PM1" type="button" class="list-group-item-uhoo flex-column text-center d-flex flex-fill align-items-center justify-content-center">
                                    PM1 <span id="Text-12" class="roboto-slab-header"  ><span id="PM1V"></span> μg/m3</span>
                                </li>
                                <li  id="PM2" type="button" class="list-group-item-uhoo flex-column text-center d-flex flex-fill align-items-center justify-content-center">
                                    PM2.5 <span id="Text-5" class="roboto-slab-header"  ><span id="PM2V"></span> μg/m3</span>
                                </li>
                                <li id="PM4" type="button" class="list-group-item-uhoo flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    PM4 <span id="Text-13" class="roboto-slab-header"  ><span id="PM4V"></span> μg/m3</span>
                                </li>
                                <li id="PM10" type="button" class="list-group-item-uhoo flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    PM10 <span id="Text-14" class="roboto-slab-header"  ><span id="PM10V"></span> μg/m3</span>
                                </li>
                                <li id="CM" type="button" class="list-group-item-uhoo flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    Carbon Monoxide <span id="Text-8" class="roboto-slab-header"  ><span id="CMV"></span> ppm</span>
                                </li>
                                <li id="AP" type="button" class="list-group-item-uhoo flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    Air Pressure <span id="Text-9" class="roboto-slab-header"  ><span id="APV"></span> hPa</span>
                                </li>
                                <li id="LI" type="button" class="list-group-item-uhoo flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    Light <span id="Text-16" class="roboto-slab-header"><span id="LIV"></span> lux</span>
                                </li>
                                <li id="NI" type="button" class="list-group-item-uhoo  flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    Noise Index <span id="Text-17" class="roboto-slab-header"  ><span id="NIV"></span>/5</span>
                                </li>
                                <li id="ND" type="button" class="list-group-item-uhoo flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    Nitrogen Dioxide <span id="Text-11" class="roboto-slab-header"  ><span id="NDV"></span>--</span>
                                </li>
                                <li id="OZ" type="button" class="list-group-item-uhoo flex-column text-center flex-fill d-flex align-items-center justify-content-center">
                                    Ozone <span id="Text-10" class="roboto-slab-header"  ><span id="OZV"></span>--</span>
                                </li>
                            
                            </ul>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-12">
                            <div class="charting" id="chart-timeline"></div>
                        </div>
                      

                    </div>         
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('javascripts')
<script>
     
     $(document).ready(function () { 
        var gID = "";
        var gSerial = @json($Serial_Number);
        localStorage.setItem('gSerial', gSerial);

        $('body').attr('data-leftbar-compact-mode', 'condensed');

        $(window).on('resize', function() {
            $('body').attr('data-leftbar-compact-mode', 'condensed');
        });
        
        function renderChart(label) {
                $('.charting').empty();
                $('.charting').attr('id', 'chart-timeline');
                var Serial_Number = @json($Serial_Number);
             
                gSerial = Serial_Number;
              //  console.log('asd11:'+gSerial)
                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                let formattedData = [];

                $.ajax({
                    type: "GET",
                    url: "{{ route('uhoo_days_in_month') }}", 
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    contentType: "application/json; charset=utf-8",
                    data: {
                        Label: label,
                        Serial_Number: Serial_Number
                    },
                    dataType: "json",
                    success: function (response) {
                        $("#chart-timeline").empty();
                        let tempArray = [];
                        response.Data.forEach(function(value) {
                            tempArray.push([parseFloat(value.timestamp)*1000, parseFloat(value.value)]);
                        });

                        var today = new Date();
                        var year = today.getFullYear();
                        var month = today.getMonth();
                        var day = today.getDate();

                        var options = {
                            series: [{
                                name: 'Hourly Data',
                                data: tempArray
                            }],
                            dataLabels: {
                                enabled: false
                            },
                            chart: {
                                id: 'hourly-chart',
                                type: 'area',
                                height: 350,
                                zoom: { autoScaleYaxis: true }
                            },
                            xaxis: {
                                type: 'datetime',
                                min: new Date(year, month, day, 0, 0, 0).getTime(),  
                                max: new Date(year, month, day, 23, 59, 59).getTime(), 
                                tickAmount: 24
                            },
                            tooltip: {
                                x: { format: 'HH:mm' }
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.7,
                                    opacityTo: 0.9,
                                    colors: ['#af2424'],
                                    stops: [0, 100]
                                }
                            }
                        };
                        var chart = new ApexCharts(document.querySelector("#chart-timeline"), options);
                        chart.render();
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            }

            $("#VI").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#VI").addClass('list-group-item-uhoo-dispay');
                renderChart('virusIndex');
            });
            $("#MI").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#MI").addClass('list-group-item-uhoo-dispay');
                renderChart('moldIndex');
            });
            $("#TE").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#TE").addClass('list-group-item-uhoo-dispay');
                renderChart('temperature');
            });
            $("#HU").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#HU").addClass('list-group-item-uhoo-dispay');
                renderChart('humidity');
            });
            $("#CD").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#CD").addClass('list-group-item-uhoo-dispay');
                renderChart('co2');
            });
            $("#TV").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#TV").addClass('list-group-item-uhoo-dispay');
                renderChart('tvoc');
            });
            $("#FO").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#FO").addClass('list-group-item-uhoo-dispay');
                renderChart('ch2o');
            });
            $("#PM1").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#PM1").addClass('list-group-item-uhoo-dispay');
                renderChart('pm1');//formaldehyde
            });
            $("#PM2").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#PM2").addClass('list-group-item-uhoo-dispay');
                renderChart('pm25');//formaldehyde
            });
            $("#PM4").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#PM4").addClass('list-group-item-uhoo-dispay');
                renderChart('pm4');//formaldehyde
            });
            $("#PM10").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#PM10").addClass('list-group-item-uhoo-dispay');
                renderChart('pm10');//formaldehyde
            });
            $("#CM").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#CM").addClass('list-group-item-uhoo-dispay');
                renderChart('co');//formaldehyde
            });
            $("#AP").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#AP").addClass('list-group-item-uhoo-dispay');
                renderChart('airPressure');//formaldehyde
            });
            $("#LI").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#LI").addClass('list-group-item-uhoo-dispay');
                renderChart('light');//formaldehyde
            });
            $("#NI").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#NI").addClass('list-group-item-uhoo-dispay');
                renderChart('sound');//formaldehyde
            });
            $("#ND").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#ND").addClass('list-group-item-uhoo-dispay');
                renderChart('no2');//formaldehyde
            });
            $("#OZ").click(function() {
                $(".list-group-item-uhoo").removeClass('list-group-item-uhoo-dispay');
                $("#OZ").addClass('list-group-item-uhoo-dispay');
                renderChart('ozone');//formaldehyde
            });


//////////////////////////////////////////////////////////////////////////////////////////////////////

        function renderLabel(){
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var Serial_Number = "";
            //console.log(gSerial);
            $.ajax({
                    type: "GET",
                    url: "{{ route('uhoo_update_val') }}", 
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    contentType: "application/json; charset=utf-8",
                    data: {
                        Serial:gSerial
                    },
                    dataType: "json",
                    success: function (response) {
                        const labelMap = {
                                            'virusIndex': '#VIV',
                                            'moldIndex': '#MIV',
                                            'temperature': '#TEV',
                                            'humidity': '#HUV',
                                            'co2': '#CDV',
                                            'tvoc': '#TVV',
                                            'ch2o': '#FOV',
                                            'pm1': '#PM1V',
                                            'pm25': '#PM2V',
                                            'pm4': '#PM4V',
                                            'pm10': '#PM10V',
                                            'co': '#CMV',
                                            'airPressure': '#APV',
                                            'light': '#LIV',
                                            'sound': '#NIV',
                        };
                        const conditionMap = {
                            'Good': 'uhoo-green',
                            'Moderate': 'uhoo-yellow',
                            'Bad': 'uhoo-red'
                        };
                        var ctr = 1;
                        response.NewData.forEach(element => {
                            const target = labelMap[element.Label];
                         
                            if (target) {
                                const conditionClass = conditionMap[element.Condition] || 'uhoo-red';
                                //console.log(target,ctr,element.Label,element.Value,conditionClass);
                                $("#Text-" + ctr).addClass(conditionClass);
                                $(target).empty().append(parseFloat(element.Value));
                            }else{
                                //ozone 10
                                //no2 11
                            }
                            
                            ctr++;
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
        }
        renderLabel();
        setInterval(renderLabel, 300000);
      
///////////////////////////////////////////////////////////////////////////////////////////////////
        $('#TE').trigger('click');

        function uhooCreateHistory(){
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            console.log("uhooCreateHistory went here");
            $.ajax({
                type: "GET",
                url: "{{ route('uhoo_create_history') }}", 
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                contentType: "application/json; charset=utf-8",
                data: {
                   
                },
                dataType: "json",
                success: function (response) {
                    console.log('inserted a history');
                    //console.log(response.NewData);
                },
                error: function (xhr, status, error) {
                    console.error('Error:',xhr,status, error,);
                }
            });
        }

        function checkAndRun() {
                let minutes = new Date().getMinutes();

                
                if (minutes % 5 === 0) {
                    uhooCreateHistory();
                    console.log('uhooCreateHistory executed');
                }
        }
     setInterval(checkAndRun, 30000);

     function ch(){
            console.log('huh');
        }
    setInterval(ch, 60000);
     });

  
    
</script>

    <script src="Modules/assets/js/esco/ReportsAnalytics.js?1728883503393"></script>
    <script src="Modules/assets/js/esco/SessionExpire.js?1728883503393"></script>
    <script src="Modules/assets/js/esco/refresh-device.js?1728883503393"></script>
        {{-- <script src="https://cdn.jsdelivr.net/npm/table2csv@1.1.6/src/table2csv.min.js"></script> --}}
@endsection
