
@extends('layouts.admin')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Wellness</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card" style="margin-bottom:14px;">
            <div class="card-body" style="padding:1rem 1.5rem;">
                <div class="d-flex justify-content-between">
                    <a href="{{route('uhooDeviceData')}}" class="btn btn-primary" style="background-color:green;border-color:green;opacity:0.7;">
                        <i class="mdi mdi-autorenew" id="refresh_uhoo_details"></i>
                    </a>
                <!--Select Option -->
                    <div class="dropdown d-flex justify-content-end">
                        <button class="form-select" style=" border-radius: 5px; width:200px;" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            Select Options
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li class="li-hover" id="li1" style=" background-color: #fff6dc;border-color: #fff6dc;">
                                <div class="form-check dropdown-item" style="width: 200px;">
                                    <input type="checkbox" class="form-check-input1" id="checkbox1" checked>
                                    <label class="form-check-label" for="customCheckcolor4" style= " font-weight:400; max-width: calc(100% - 30px); display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        Show All </label>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-lg-4">
        <div class="card tilebox-one" style="border-radius:0.65rem; margin-bottom:14px;">
            <div class="card-body">
                <div id="sortGreen" style="color:#6c757d;cursor:pointer;">
                    <i class='mdi mdi-leaf float-end' style="color: green"></i>
                    <h6 class="text-uppercase mt-0">Good</h6>
                    <h2 class="my-2 roboto-slab-header" id=""> <span class="" style="color: green" id="TotGood">{{ $green }}</span> / {{ $total }}</h2>
                    <div class="col d-flex justify-content-between">
                        <span class="text-success me-2">
                        </span>
                        <div class="float-end" id="tooltip-container2">
                            <span class="text-nowrap float-end" data-bs-container="#tooltip-container2" data-bs-toggle="tooltip" data-bs-placement="left" title="Good Zone: Looks good on your side">Green Zone</span>
                        </div>
                    </div>
                </div>
            </div> <!-- end card-body-->
        </div>
    </div>

    <div class="col-xl-4 col-lg-4">
        <div class="card tilebox-one" style="border-radius:0.65rem; margin-bottom:14px;">
            <div class="card-body">
                <div id="sortYellow"  style="color:#6c757d;cursor:pointer;">
                <i class='mdi mdi-leaf float-end'style="color:rgb(235, 194, 12)"></i>
                <h6 class="text-uppercase mt-0">Moderate</h6>
                <h2 class="my-2 roboto-slab-header" id=""><span  id="TotMod" style="color: rgb(235, 194, 12)">{{ $yellow }}</span> / {{ $total }}</h2>
                <div class="col d-flex justify-content-between">
                    <span class=" me-2" style="color: rgb(235, 194, 12)">
                        {{-- <span class="mdi mdi-arrow-down-bold"></span> 11.27% --}}
                    </span>
                    <div class="float-end" id="tooltip-container2">
                        <span class="text-nowrap float-end" data-bs-container="#tooltip-container2" data-bs-toggle="tooltip" data-bs-placement="left" title="Warning Zone: Observation is recommended">Orange Zone</span>
                    </div>
                </div>
                </div>
            </div> <!-- end card-body-->
        </div>
    </div>

    <div class="col-xl-4 col-lg-4">
        <div class="card tilebox-one" style="border-radius:0.65rem; margin-bottom:14px;">
            <div class="card-body position-relative">
                <div class="position-relative" id="sortRed" style="color:#6c757d;cursor:pointer;">
                <i class='mdi mdi-leaf-off float-end' style="color:rgb(175, 36, 36)"></i>
                <h6 class="text-uppercase mt-0 " >Bad</h6>
                <div class="position-relative">
                    {{-- <h2 style="color:transparent"> s</h2> --}}
                    <h2 class="my-2 roboto-slab-header "  id=""><span  id="TotMod" style="color:rgb(163, 62, 62)">{{ $red }}</span> / {{ $total }}</h2>

                </div>
                <div class="col d-flex justify-content-between">
                    <span class="text-danger me-2 d-flex float-start">
                      
                    </span>
                    <div class="float-end" id="tooltip-container2">
                        <span class="text-nowrap float-end" data-bs-container="#tooltip-container2" data-bs-toggle="tooltip" data-bs-placement="left" title="Danger Zone: Needs your attention">Red Zone</span>
                    </div>
                </div>
                </div>
            </div> <!-- end card-body-->
        </div>
    </div>
</div>

<div class="row" id="devices">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <span class="outfit-1">DEVICES</span>
                </div>
                <ul class="list-group" id="ulDevices">
                  
                </ul>
            <img src="" alt="" id="img1">
                   
            </div>
        </div>
    </div>
</div>
<div class="row">
   
</div>



<div class="row">
       
</div>
<!-- end row -->
@endsection
@section('javascripts')
<script>
    let isClicked = false;

  $(document).ready(function () { 
    $("#ulDevices").empty();
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    //var baseUrl = "{{ route('uhooDisplay', ['kdsartkn231nkjh1k23hkjn12' => '','DeviceName' => '']) }}";
    var baseUrl = "{{ route('uhooDisplay', ['kdsartkn231nkjh1k23hkjn12' => '__SERIAL_NUMBER__', 'DeviceName' => '__DEVICE_NAME__']) }}";
  
    $.ajax({
            type: "GET",
            url: "{{ route('uhoo_all_device') }}", // Ensure correct Blade syntax spacing
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Ensure CSRF token is correctly passed
            },
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify({
            }),
            dataType: "json",
            success: function (response) {
                var markup='';
                var id=0;
                var condition ='';
            
                response.cond.forEach(function(value,index) {
                    //console.log(value)
                    id=0;
                    var condition ='';
                    markup = "<a href='" + baseUrl.replace('__SERIAL_NUMBER__', value[id].Serial_Number).replace('__DEVICE_NAME__', value[id].Device_Name) + "'>";
               // markup = "<a href='" + baseUrl +  value[id].Serial_Number + "?"+"" + value[id].Device_Name + "'>";
                    markup= markup+ "<li type='button' id='' style='position: relative' class='list-group-item'><img style='width: 20px;' class='card-img-top' src='/Modules/assets/images/uHoo-Maaster-Logo.png' alt='Card image cap'>&nbsp;&nbsp";
                    markup= markup+value[id].Device_Name;
                    markup= markup+"<div class='position-absolute' style='bottom:10px;left:250px;'>"
                    if (Array.isArray(value)) {
                        value.forEach(({ Condition, Label }) => {
                            const condition = (Condition === "Good") ? 'color-good' :
                                              (Condition === "Moderate") ? 'color-moderate' :
                                              'color-bad';
                           // console.log(Label,Condition);
                            const imageMap = {
                                virusIndex: 'aura_virus.35848cce.svg',
                                moldIndex: 'aura_mold.svg',
                                temperature: 'aura_temp.svg',
                                humidity: 'aura_humidity.svg',
                                pm25: 'aura-dust.svg',
                                tvoc: 'aura_voc.svg',
                                co2: 'aura_co2.svg',
                                co: 'aura_co.svg',
                                airPressure: 'aura_pressure.svg',
                                pm1: 'aura_pm1.svg',
                                pm4: 'aura_pm4.svg',
                                pm10: 'aura_pm10.svg',
                                ch2o: 'formaldehyde.svg',
                                light: 'light.svg',
                                sound: 'sound.svg'
                            };

                            if (imageMap[Label]) {
                                markup += ` <div class="uhoo-tooltip">
                                                <img 
                                                    class="${condition}" 
                                                    src="../Modules/assets/images/uhoo/${imageMap[Label]}" 
                                                    alt="SVG Image" 
                                                    width="20" 
                                                    height="20"
                                                >
                                                <span class="tooltiptext">${Label}</span>
                                            </div>`;
                                // markup += `<img data-title="${Label}" class="${condition} uhoo-tooltip" src="../Modules/assets/images/uhoo/${imageMap[Label]}" alt="SVG Image" width="20" height="20">`;
                               
                               // markup += `<img class="${condition} " src="../Modules/assets/images/uhoo/${imageMap[Label]}" alt="SVG Image" width="20" height="20">`;

                            }
                        });
                    }
                    markup= markup+"</div>"
                    markup= markup+"<div class='float-end'>Status</div>"   //#6c757d
                    if(value[id].Status == "Online"){
                        markup= markup+`<div class='spinner-grow uhoo-tooltip float-end me-2' style='color:#339933;margin-top:2px;width: 15px; height:15px;animation-duration: 3s;' role='status'>  <span class="tooltiptext">Online Device</span></div>` 
                    }else{
                        markup= markup+`<div  class='spinner-grow  uhoo-tooltip float-end me-2' style='color:#bf5050;margin-top:2px;width: 15px; height:15px;animation-duration: 3s;' role='status'><span class="tooltiptext">Offline Device</span></div>`
                    }  
                    markup= markup+" </li>"
                    markup= markup+" </a>"
                    $("#ulDevices").append(markup);
                    id++;
                });
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
            }
        });

        function uhooUpdate(){
            
            $.ajax({
            type: "GET",
            url: "{{ route('uhooDeviceData') }}", // Ensure correct Blade syntax spacing
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Ensure CSRF token is correctly passed
            },
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify({
            }),
            dataType: "json",
            success: function (response) {
              
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
            }
        });
        }
  });
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
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
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
  $('#li1').click(function(event) {
    event.stopPropagation();
    $('#checkbox1').prop('checked', !$('#checkbox1').prop('unchecked'));
    $("#devices").show();
  });
  $('#checkbox1').click(function(event) {
    event.stopPropagation();
    
    $('#checkbox1').prop('unchecked', !$('#checkbox1').prop('checked'));

    if ($('#checkbox1').prop('checked')) {
        $('#devices').show();   
    } else {
        $('#devices').hide();
    }
   
  });

  $("#sortGreen").click(function(event){
    event.stopPropagation();
    if (isClicked) {
        return; // Ignore clicks if already clicked
    }
    isClicked = true;
    $("#ulDevices").empty();
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    //var baseUrl = "{{ route('uhooDisplay', ['kdsartkn231nkjh1k23hkjn12' => '']) }}";
    var baseUrl = "{{ route('uhooDisplay', ['kdsartkn231nkjh1k23hkjn12' => '__SERIAL_NUMBER__', 'DeviceName' => '__DEVICE_NAME__']) }}";

        $.ajax({
            type: "GET",
            url: "{{ route('uhoo_sort') }}", 
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            contentType: "application/json; charset=utf-8",
            data: {
                color: 'Good' 
            },
            dataType: "json",
            success: function (response) {
                var markup='';
                var id=0;
                var condition ='';
                if (Array.isArray(response.green) && response.green.length === 0) {
                }else{
                    response.green.forEach(function(value,index) {
                        id=0;
                        var condition ='';

                       // markup = "<a href='" + baseUrl  + value[id].Serial_Number + "'>";
                        markup = "<a href='" + baseUrl.replace('__SERIAL_NUMBER__', value[id].Serial_Number).replace('__DEVICE_NAME__', value[id].Device_Name) + "'>";
                            markup= markup+ "<li type='button' id='device1' style='position: relative' class='list-group-item'><img style='width: 20px;' class='card-img-top' src='/Modules/assets/images/uHoo-Maaster-Logo.png' alt='Card image cap'>&nbsp;&nbsp";
                        markup= markup+value[id].Device_Name;
                        markup= markup+"<div class='position-absolute' style='bottom:10px;left:250px;'>"


                        if (Array.isArray(value)) {
                            value.forEach(({ Condition, Label }) => {
                                const condition = (Condition === "Good") ? 'color-good' :
                                                (Condition === "Moderate") ? 'color-moderate' :
                                                'color-bad';

                                const imageMap = {
                                    virusIndex: 'aura_virus.35848cce.svg',
                                    moldIndex: 'aura_mold.svg',
                                    temperature: 'aura_temp.svg',
                                    humidity: 'aura_humidity.svg',
                                    pm25: 'aura-dust.svg',
                                    tvoc: 'aura_voc.svg',
                                    co2: 'aura_co2.svg',
                                    co: 'aura_co.svg',
                                    airPressure: 'aura_pressure.svg',
                                    pm1: 'aura_pm1.svg',
                                    pm4: 'aura_pm4.svg',
                                    pm10: 'aura_pm10.svg',
                                    ch2o: 'formaldehyde.svg',
                                    light: 'light.svg',
                                    sound: 'sound.svg'
                                };

                                if (imageMap[Label]) {
                                    markup += ` <div class="uhoo-tooltip">
                                                    <img 
                                                        class="${condition}" 
                                                        src="../Modules/assets/images/uhoo/${imageMap[Label]}" 
                                                        alt="SVG Image" 
                                                        width="20" 
                                                        height="20"
                                                    >
                                                    <span class="tooltiptext">${Label}</span>
                                                </div>`;
                                    // markup += `<img class="${condition}" src="../Modules/assets/images/uhoo/${imageMap[Label]}" alt="SVG Image" width="20" height="20">`;
                                }
                            });
                        }
                        markup= markup+"</div>"
                        markup= markup+"<div class='float-end'>Status</div>"    
                        if(value[id].Status == "Online"){
                        markup= markup+`<div class='spinner-grow uhoo-tooltip float-end me-2' style='color:#339933;margin-top:2px;width: 15px; height:15px;animation-duration: 3s;' role='status'>  <span class="tooltiptext">Online Device</span></div>` 
                    }else{
                        markup= markup+`<div  class='spinner-grow  uhoo-tooltip float-end me-2' style='color:#bf5050;margin-top:2px;width: 15px; height:15px;animation-duration: 3s;' role='status'><span class="tooltiptext">Offline Device</span></div>`
                    }   
                       // markup= markup+"<div data-bs-container='#tooltip-container2' data-bs-toggle='tooltip' data-bs-placement='top' title='Offline Device' class='spinner-grow  float-end me-2' style='color:#339933;margin-top:2px;width: 15px; height:15px;animation-duration: 2s;' role='status'></div>" 
                        markup= markup+" </li>"
                        markup= markup+" </a>"
                        $("#ulDevices").append(markup);
                        id++;
                      
                    });
                   
                }
                isClicked = false;
            },
            error: function (xhr, status, error) {
                isClicked = false;
                console.error('Error:', error);
            }
        });

  });  

  $("#sortYellow").click(function(event){
    event.stopPropagation();
    if (isClicked) {
        return; // Ignore clicks if already clicked
    }
    isClicked = true;
    $("#ulDevices").empty();
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    //var baseUrl = "{{ route('uhooDisplay', ['kdsartkn231nkjh1k23hkjn12' => '']) }}";
    var baseUrl = "{{ route('uhooDisplay', ['kdsartkn231nkjh1k23hkjn12' => '__SERIAL_NUMBER__', 'DeviceName' => '__DEVICE_NAME__']) }}";

            $.ajax({
            type: "GET",
            url: "{{ route('uhoo_sort') }}", 
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            contentType: "application/json; charset=utf-8",
            data: {
                color: 'Moderate' 
            },
            dataType: "json",
            success: function (response) {
            
                var markup='';
                var id=0;
                var condition ='';
                response.cond.forEach(function(value,index) {
                    id=0;
                    var condition ='';
                    //markup = "<a href='" + baseUrl  + value[id].Serial_Number + "'>";
                        markup = "<a href='" + baseUrl.replace('__SERIAL_NUMBER__', value[id].Serial_Number).replace('__DEVICE_NAME__', value[id].Device_Name) + "'>";
                        markup= markup+ "<li type='button' id='' style='position: relative' class='list-group-item'><img style='width: 20px;' class='card-img-top' src='/Modules/assets/images/uHoo-Maaster-Logo.png' alt='Card image cap'>&nbsp;&nbsp";
                    markup= markup+value[id].Device_Name;
                    markup= markup+"<div class='position-absolute' style='bottom:10px;left:250px;'>"


                    if (Array.isArray(value)) {
                        value.forEach(({ Condition, Label }) => {
                            const condition = (Condition === "Good") ? 'color-good' :
                                              (Condition === "Moderate") ? 'color-moderate' :
                                              'color-bad';

                            const imageMap = {
                                virusIndex: 'aura_virus.35848cce.svg',
                                moldIndex: 'aura_mold.svg',
                                temperature: 'aura_temp.svg',
                                humidity: 'aura_humidity.svg',
                                pm25: 'aura-dust.svg',
                                tvoc: 'aura_voc.svg',
                                co2: 'aura_co2.svg',
                                co: 'aura_co.svg',
                                airPressure: 'aura_pressure.svg',
                                pm1: 'aura_pm1.svg',
                                pm4: 'aura_pm4.svg',
                                pm10: 'aura_pm10.svg',
                                ch2o: 'formaldehyde.svg',
                                light: 'light.svg',
                                sound: 'sound.svg'
                            };

                            if (imageMap[Label]) {
                                markup += ` <div class="uhoo-tooltip">
                                                <img 
                                                    class="${condition}" 
                                                    src="../Modules/assets/images/uhoo/${imageMap[Label]}" 
                                                    alt="SVG Image" 
                                                    width="20" 
                                                    height="20"
                                                >
                                                <span class="tooltiptext">${Label}</span>
                                            </div>`;
                                // markup += `<img class="${condition}" src="../Modules/assets/images/uhoo/${imageMap[Label]}" alt="SVG Image" width="20" height="20">`;
                            }
                        });
                    }
                    markup= markup+"</div>"
                    markup= markup+"<div class='float-end'>Status</div>" 
                    if(value[id].Status == "Online"){
                        markup= markup+`<div class='spinner-grow uhoo-tooltip float-end me-2' style='color:#339933;margin-top:2px;width: 15px; height:15px;animation-duration: 3s;' role='status'>  <span class="tooltiptext">Online Device</span></div>` 
                    }else{
                        markup= markup+`<div  class='spinner-grow  uhoo-tooltip float-end me-2' style='color:#bf5050;margin-top:2px;width: 15px; height:15px;animation-duration: 3s;' role='status'><span class="tooltiptext">Offline Device</span></div>`
                    }      
                   // markup= markup+"<div data-bs-container='#tooltip-container2' data-bs-toggle='tooltip' data-bs-placement='top' title='Offline Device' class='spinner-grow  float-end me-2' style='color:#339933;margin-top:2px;width: 15px; height:15px;animation-duration: 2s;' role='status'></div>" 
                    markup= markup+" </li>"
                    markup= markup+" </a>"
                    $("#ulDevices").append(markup);
                    id++;
                    isClicked = false;
                });
            },
            error: function (xhr, status, error) {
                isClicked = false;
                console.error('Error:', error);
            }
        });

  }); 

  $("#sortRed").click(function(event){
    event.stopPropagation();
    if (isClicked) {
        return; // Ignore clicks if already clicked
    }
    isClicked = true;
    $("#ulDevices").empty();
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    //var baseUrl = "{{ route('uhooDisplay', ['kdsartkn231nkjh1k23hkjn12' => '']) }}";
    var baseUrl = "{{ route('uhooDisplay', ['kdsartkn231nkjh1k23hkjn12' => '__SERIAL_NUMBER__', 'DeviceName' => '__DEVICE_NAME__']) }}";
   
        $.ajax({
            type: "GET",
            url: "{{ route('uhoo_sort') }}", // Ensure correct Blade syntax spacing
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Ensure CSRF token is correctly passed
            },
            contentType: "application/json; charset=utf-8",
            data: {
                color: 'Bad' // No need for JSON.stringify here
            },
            dataType: "json",
            success: function (response) {
                var markup='';
                var id=0;
                var condition ='';
               // console.log(response);
                response.cond.forEach(function(value,index) {
                    id=0;
                    var condition ='';
                  //  markup = "<a href='" + baseUrl  + value[id].Serial_Number + "'>";
                        markup = "<a href='" + baseUrl.replace('__SERIAL_NUMBER__', value[id].Serial_Number).replace('__DEVICE_NAME__', value[id].Device_Name) + "'>";
                        markup= markup+ "<li type='button' id='' style='position: relative' class='list-group-item'><img style='width: 20px;' class='card-img-top' src='/Modules/assets/images/uHoo-Maaster-Logo.png' alt='Card image cap'>&nbsp;&nbsp";
                    markup= markup+value[id].Device_Name;
                    markup= markup+"<div class='position-absolute' style='bottom:10px;left:250px;'>"

                    if (Array.isArray(value)) {
                        value.forEach(({ Condition, Label }) => {
                            const condition = (Condition === "Good") ? 'color-good' :
                                              (Condition === "Moderate") ? 'color-moderate' :
                                              'color-bad';

                            const imageMap = {
                                virusIndex: 'aura_virus.35848cce.svg',
                                moldIndex: 'aura_mold.svg',
                                temperature: 'aura_temp.svg',
                                humidity: 'aura_humidity.svg',
                                pm25: 'aura-dust.svg',
                                tvoc: 'aura_voc.svg',
                                co2: 'aura_co2.svg',
                                co: 'aura_co.svg',
                                airPressure: 'aura_pressure.svg',
                                pm1: 'aura_pm1.svg',
                                pm4: 'aura_pm4.svg',
                                pm10: 'aura_pm10.svg',
                                ch2o: 'formaldehyde.svg',
                                light: 'light.svg',
                                sound: 'sound.svg'
                            };
                            if (imageMap[Label]) {
                                markup += ` <div class="uhoo-tooltip">
                                                <img 
                                                    class="${condition}" 
                                                    src="../Modules/assets/images/uhoo/${imageMap[Label]}" 
                                                    alt="SVG Image" 
                                                    width="20" 
                                                    height="20"
                                                >
                                                <span class="tooltiptext">${Label}</span>
                                            </div>`;
                            }
                        });
                    }
                    markup= markup+"</div>"
                    markup= markup+"<div class='float-end'>Status</div>"     
                    if(value[id].Status == "Online"){
                        markup= markup+`<div class='spinner-grow uhoo-tooltip float-end me-2' style='color:#339933;margin-top:2px;width: 15px; height:15px;animation-duration: 3s;' role='status'>  <span class="tooltiptext">Online Device</span></div>` 
                    }else{
                        markup= markup+`<div  class='spinner-grow  uhoo-tooltip float-end me-2' style='color:#bf5050;margin-top:2px;width: 15px; height:15px;animation-duration: 3s;' role='status'><span class="tooltiptext">Offline Device</span></div>`
                    }  
                   // markup= markup+"<div data-bs-container='#tooltip-container2' data-bs-toggle='tooltip' data-bs-placement='top' title='Offline Device' class='spinner-grow  float-end me-2' style='color:#339933;margin-top:2px;width: 15px; height:15px;animation-duration: 2s;' role='status'></div>" 
                    markup= markup+" </li>"
                    markup= markup+" </a>"
                    $("#ulDevices").append(markup);
                    id++;
                    isClicked = false;
                });
            },
            error: function (xhr, status, error) {
                isClicked = false;
                console.error('Error:', error);
            }
        });

  
  }); 

</script>
    <script src="Modules/assets/js/esco/ReportsAnalytics.js?1728883503393"></script>
    <script src="Modules/assets/js/esco/SessionExpire.js?1728883503393"></script>
    <script src="Modules/assets/js/esco/refresh-device.js?1728883503393"></script>
        <script src="https://cdn.jsdelivr.net/npm/table2csv@1.1.6/src/table2csv.min.js"></script>
      
@endsection
