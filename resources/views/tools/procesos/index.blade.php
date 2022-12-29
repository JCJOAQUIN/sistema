@extends('layouts.child_module')

@section('css')
	<link rel="stylesheet" type="text/css" href="{{ asset('js/jstree/themes/default/style.min.css') }}">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.11.1/cr-1.5.4/fh-3.1.9/r-2.2.9/sc-2.0.5/datatables.min.css"/>
@endsection
@section('data')
	@php
		$obj = Auth::user()->canUploadFiles(306)->pluck('permission')->toArray();
	@endphp
	@component("components.inputs.input-text")
		@slot('classEx') hidden @endslot
		@slot("attributeEx") type="file" id="upload_files" multiple @endslot
	@endcomponent
	<div class="row flex flex-col md:grid md:grid-cols-12 bg-gray-100">
		<div class="col-span-4 order-first md:order-none">
			<div class="flex">
				@if(in_array(1, $obj))
					@component("components.buttons.button", ["buttonElement" => "a", "variant" => "transparent"])
						@slot("classEx") ml-4 mr-2 @endslot
						@slot("attributeEx") id="new_folder" href="#" alt="Nueva carpeta" title="Nueva carpeta" @endslot
						<svg width="24" height="24" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
							<path d="M161,31L161,126L256,126L289,75L256,31L161,31Z" style="fill:rgb(255,177,0);fill-rule:nonzero;"/>
							<rect x="256" y="31" width="227" height="95" style="fill:rgb(255,145,0);fill-rule:nonzero;"/>
							<path d="M207.792,91L167.792,0L0,0L0,469L256,469L299.5,268L256,91L207.792,91Z" style="fill:rgb(255,205,0);fill-rule:nonzero;"/>
							<rect x="256" y="91" width="256" height="378" style="fill:rgb(255,177,0);fill-rule:nonzero;"/>
							<path d="M270,398C270,460.859 321.141,512 384,512L396,396L384,284C321.141,284 270,335.141 270,398Z" style="fill:rgb(251,250,250);fill-rule:nonzero;"/>
							<path d="M384,284L384,512C446.859,512 498,460.859 498,398C498,335.141 446.859,284 384,284Z" style="fill:rgb(223,220,224);fill-rule:nonzero;"/>
							<path d="M369,353L369,383L339,383L339,413L369,413L369,443L384,443L392,397L384,353L369,353Z" style="fill:rgb(41,204,149);fill-rule:nonzero;"/>
							<path d="M399,383L399,353L384,353L384,443L399,443L399,413L429,413L429,383L399,383Z" style="fill:rgb(31,153,153);fill-rule:nonzero;"/>
						</svg>
					@endcomponent
					@component("components.buttons.button", ["buttonElement" => "a", "variant" => "transparent"])
						@slot("classEx") m-2 @endslot
						@slot("attributeEx") id="rename_folder" href="#" alt="Renombrar carpeta" title="Renombrar carpeta" @endslot
						<svg width="24" height="24" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
							<path d="M495.83,143.7L495.83,363.51C495.83,378.69 483.53,390.99 468.34,390.99L49.33,390.99C34.14,390.99 21.84,378.69 21.84,363.51L21.84,143.7C21.84,128.52 34.14,116.22 49.33,116.22L468.34,116.22C483.53,116.22 495.83,128.52 495.83,143.7Z" style="fill:rgb(228,235,242);fill-rule:nonzero;"/>
							<path d="M495.83,143.7L495.83,363.51C495.83,378.69 483.53,390.99 468.34,390.99L256,390.99L256,116.22L468.34,116.22C483.53,116.22 495.83,128.52 495.83,143.7Z" style="fill:rgb(204,214,231);fill-rule:nonzero;"/>
							<path d="M256,375.99L53.11,375.99C40.36,375.99 30,365.63 30,352.89L30,159.11C30,146.37 40.36,136.01 53.11,136.01L256,136.01L270.496,121.897L256,106.01L53.11,106.01C23.82,106.01 -0,129.83 -0,159.11L-0,352.89C-0,382.17 23.82,405.99 53.11,405.99L256,405.99L274.998,390.99L256,375.99Z" style="fill:rgb(153,221,255);fill-rule:nonzero;"/>
							<path d="M512,159.11L512,352.89C512,382.17 488.177,405.99 458.894,405.99L396.067,405.99L396.067,375.99L458.894,375.99C471.635,375.99 481.997,365.63 481.997,352.89L481.997,159.11C481.997,146.37 471.636,136.01 458.894,136.01L395.947,136.01L395.717,106.01L458.894,106.01C488.177,106.01 512,129.83 512,159.11Z" style="fill:rgb(128,191,255);fill-rule:nonzero;"/>
							<path d="M172.05,210.392C163.765,210.392 157.048,217.108 157.048,225.392L157.048,227.908C146.681,221.132 134.312,217.176 121.029,217.176C84.623,217.176 55.006,246.79 55.006,283.192C55.006,319.594 84.624,349.208 121.029,349.208C134.312,349.208 146.682,345.252 157.048,338.476L157.048,340.991C157.048,349.275 163.765,355.991 172.05,355.991C180.335,355.991 187.052,349.275 187.052,340.991L187.052,225.392C187.051,217.107 180.335,210.392 172.05,210.392ZM121.029,319.207C101.168,319.207 85.009,303.05 85.009,283.191C85.009,263.332 101.168,247.175 121.029,247.175C140.89,247.175 157.048,263.332 157.048,283.191C157.048,303.05 140.89,319.207 121.029,319.207Z" style="fill:rgb(170,153,255);fill-rule:nonzero;"/>
							<path d="M256,315.21C247.44,307.52 242.06,296.37 242.06,283.99C242.06,271.61 247.44,260.47 256,252.78L274.998,233.709L256,217.71C251.05,219.81 246.38,222.46 242.06,225.57L242.06,171.01C242.06,162.73 235.34,156.01 227.06,156.01C218.77,156.01 212.05,162.73 212.05,171.01L212.05,340.99C212.05,349.27 218.77,355.99 227.06,355.99C234.88,355.99 241.29,350 241.99,342.37C246.33,345.5 251.02,348.17 256,350.29L274.998,338.521L256,315.21Z" style="fill:rgb(170,153,255);fill-rule:nonzero;"/>
							<path d="M356.06,283.99C356.06,323.69 323.76,355.99 284.06,355.99C274.11,355.99 264.63,353.96 256,350.29L256,315.21C263.44,321.91 273.28,325.99 284.06,325.99C307.22,325.99 326.06,307.15 326.06,283.99C326.06,260.84 307.22,242 284.06,242C273.28,242 263.44,246.08 256,252.78L256,217.71C264.62,214.03 274.1,212 284.06,212C323.76,212 356.06,244.29 356.06,283.99Z" style="fill:rgb(153,102,255);fill-rule:nonzero;"/>
							<path d="M512,159.11L512,352.89C512,382.17 488.18,405.99 458.89,405.99L256,405.99L256,375.99L458.89,375.99C471.64,375.99 482,365.63 482,352.89L482,159.11C482,146.37 471.64,136.01 458.89,136.01L256,136.01L256,106.01L458.89,106.01C488.18,106.01 512,129.83 512,159.11Z" style="fill:rgb(128,191,255);fill-rule:nonzero;"/>
							<path d="M447.913,464.39C447.913,472.67 441.202,479.39 432.911,479.39C418.519,479.39 405.478,473.48 396.067,463.98C386.666,473.48 373.624,479.39 359.223,479.39C350.942,479.39 344.221,472.67 344.221,464.39C344.221,456.1 350.942,449.39 359.223,449.39C371.274,449.39 381.066,439.59 381.066,427.55L381.066,84.45C381.066,72.41 371.275,62.61 359.223,62.61C350.942,62.61 344.221,55.9 344.221,47.61C344.221,39.33 350.942,32.61 359.223,32.61C373.625,32.61 386.666,38.52 396.067,48.02C405.478,38.52 418.52,32.61 432.911,32.61C441.202,32.61 447.913,39.33 447.913,47.61C447.913,55.9 441.202,62.61 432.911,62.61C420.87,62.61 411.068,72.41 411.068,84.45L411.068,427.55C411.068,439.59 420.869,449.39 432.911,449.39C441.202,449.39 447.913,456.1 447.913,464.39Z" style="fill:rgb(87,77,140);fill-rule:nonzero;"/>
							<path d="M447.913,464.39C447.913,472.67 441.202,479.39 432.911,479.39C418.519,479.39 405.478,473.48 396.067,463.98L396.067,48.02C405.478,38.52 418.52,32.61 432.911,32.61C441.202,32.61 447.913,39.33 447.913,47.61C447.913,55.9 441.202,62.61 432.911,62.61C420.87,62.61 411.068,72.41 411.068,84.45L411.068,427.55C411.068,439.59 420.869,449.39 432.911,449.39C441.202,449.39 447.913,456.1 447.913,464.39Z" style="fill:rgb(62,51,115);fill-rule:nonzero;"/>
						</svg>
					@endcomponent
					@component("components.buttons.button", ["buttonElement" => "a", "variant" => "transparent"])
						@slot("classEx") m-2 @endslot
						@slot("attributeEx") id="remove_folder" class="m-4" href="#" alt="Eliminar carpeta" title="Eliminar carpeta" @endslot
						<svg height="24" viewBox="0 0 512 512" width="24" xmlns="http://www.w3.org/2000/svg">
							<path d="m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm0 0" fill="#f44336"/>
							<path d="m350.273438 320.105469c8.339843 8.34375 8.339843 21.824219 0 30.167969-4.160157 4.160156-9.621094 6.25-15.085938 6.25-5.460938 0-10.921875-2.089844-15.082031-6.25l-64.105469-64.109376-64.105469 64.109376c-4.160156 4.160156-9.621093 6.25-15.082031 6.25-5.464844 0-10.925781-2.089844-15.085938-6.25-8.339843-8.34375-8.339843-21.824219 0-30.167969l64.109376-64.105469-64.109376-64.105469c-8.339843-8.34375-8.339843-21.824219 0-30.167969 8.34375-8.339843 21.824219-8.339843 30.167969 0l64.105469 64.109376 64.105469-64.109376c8.34375-8.339843 21.824219-8.339843 30.167969 0 8.339843 8.34375 8.339843 21.824219 0 30.167969l-64.109376 64.105469zm0 0" fill="#fafafa"/>
						</svg>
					@endcomponent
				@endif
			</div>
		</div>
		<div class="col-span-8 order-last md:order-none">
			<div class="flex">
				@if(in_array(1, $obj))
					@component("components.buttons.button", ["buttonElement" => "a", "variant" => "transparent"])
						@slot("classEx") ml-4 mr-2 @endslot
						@slot("attributeEx") id="upload_file" href="#" alt="Cargar archivo" title="Cargar archivo" @endslot
						<svg width="24" height="24" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
							<path d="M392.836,0L78.382,0C67.369,0 58.408,8.96 58.408,19.974L58.408,399.505C58.408,410.519 67.368,419.479 78.382,419.479L98.594,419.479C99.98,419.479 101.303,418.904 102.249,417.891L411.749,86.409C412.613,85.483 413.094,84.263 413.094,82.997L413.094,20.258C413.095,9.088 404.007,-0 392.836,-0Z" style="fill:rgb(255,105,134);fill-rule:nonzero;"/>
							<path d="M453.592,123.49L453.592,434.5C453.592,445.52 444.632,454.48 433.622,454.48L113.572,454.48C102.552,454.48 93.592,445.52 93.592,434.5L93.592,57.47C93.592,46.46 102.552,37.5 113.572,37.5L367.592,37.5L376.832,46.74L453.592,123.49Z" style="fill:rgb(217,249,248);fill-rule:nonzero;"/>
							<path d="M453.592,123.49L453.592,434.5C453.592,445.52 444.632,454.48 433.622,454.48L113.572,454.48C102.552,454.48 93.592,445.52 93.592,434.5L93.592,429.48L412.892,429.48C424.042,429.48 433.082,420.44 433.082,409.28L433.082,123.49L376.832,46.74L453.592,123.49Z" style="fill:rgb(193,234,244);fill-rule:nonzero;"/>
							<path d="M453.592,123.49L433.082,123.49L374.172,123.48C370.542,123.48 367.592,120.54 367.592,116.9L367.592,37.5C368.922,37.5 370.192,38.02 371.132,38.96L452.132,119.96C453.072,120.9 453.592,122.17 453.592,123.49Z" style="fill:rgb(174,201,214);fill-rule:nonzero;"/>
							<path d="M453.592,123.49L433.082,123.49L371.132,38.96L452.132,119.96C453.072,120.9 453.592,122.17 453.592,123.49Z" style="fill:rgb(143,178,188);fill-rule:nonzero;"/>
							<path d="M363.491,264.802L328.044,264.802C323.902,264.802 320.544,268.159 320.544,272.302C320.544,276.445 323.902,279.802 328.044,279.802L363.491,279.802C367.633,279.802 370.991,276.445 370.991,272.302C370.991,268.159 367.633,264.802 363.491,264.802ZM292.747,264.802L184.069,264.802C179.927,264.802 176.569,268.159 176.569,272.302C176.569,276.445 179.927,279.802 184.069,279.802L292.747,279.802C296.889,279.802 300.247,276.445 300.247,272.302C300.247,268.159 296.889,264.802 292.747,264.802ZM363.491,219.688L184.069,219.688C179.927,219.688 176.569,223.045 176.569,227.188C176.569,231.331 179.927,234.688 184.069,234.688L363.491,234.688C367.633,234.688 370.991,231.331 370.991,227.188C370.991,223.045 367.633,219.688 363.491,219.688ZM184.069,190.456L363.491,190.456C367.633,190.456 370.991,187.099 370.991,182.956C370.991,178.813 367.633,175.456 363.491,175.456L184.069,175.456C179.927,175.456 176.569,178.813 176.569,182.956C176.569,187.099 179.927,190.456 184.069,190.456Z" style="fill:rgb(174,201,214);fill-rule:nonzero;"/>
							<path d="M367.982,417.81C367.982,469.74 325.722,512 273.782,512C269.722,512 265.722,511.74 261.802,511.24L261.762,511.24C215.472,505.31 179.582,465.67 179.582,417.81C179.582,369.94 215.472,330.3 261.762,324.37L261.802,324.37C265.722,323.87 269.722,323.61 273.782,323.61C325.722,323.61 367.982,365.87 367.982,417.81Z" style="fill:rgb(255,105,134);fill-rule:nonzero;"/>
							<path d="M367.982,417.81C367.982,469.74 325.722,512 273.782,512C269.722,512 265.722,511.74 261.802,511.24C308.092,505.31 343.982,465.67 343.982,417.81C343.982,369.94 308.092,330.3 261.802,324.37C265.722,323.87 269.722,323.61 273.782,323.61C325.722,323.61 367.982,365.87 367.982,417.81Z" style="fill:rgb(234,52,135);fill-rule:nonzero;"/>
							<path d="M311.881,410.598C308.952,413.528 304.203,413.527 301.275,410.599L281.281,390.606L281.281,463.112C281.281,467.255 277.923,470.612 273.781,470.612C269.639,470.612 266.281,467.255 266.281,463.112L266.281,390.606L246.287,410.599C243.358,413.527 238.609,413.528 235.681,410.598C232.752,407.669 232.752,402.92 235.681,399.992L268.478,367.196C269.942,365.732 271.862,365 273.781,365C275.7,365 277.62,365.732 279.084,367.196L311.881,399.992C314.81,402.92 314.81,407.669 311.881,410.598Z" style="fill:white;fill-rule:nonzero;"/>
						</svg>
					@endcomponent
				@endif
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "transparent"])
					@slot("classEx") m-2 disabled @endslot
					@slot("attributeEx") id="download_file" href="#" alt="Descargar" title="Descargar" @endslot
					<svg width="24" height="24" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
						<path d="M392.836,0L78.382,0C67.369,0 58.408,8.96 58.408,19.974L58.408,399.505C58.408,410.519 67.368,419.479 78.382,419.479L98.594,419.479C99.98,419.479 101.303,418.904 102.249,417.891L411.749,86.409C412.613,85.483 413.094,84.263 413.094,82.997L413.094,20.258C413.095,9.088 404.007,-0 392.836,-0Z" style="fill:rgb(79,232,188);fill-rule:nonzero;"/>
						<path d="M453.592,123.49L453.592,434.5C453.592,445.52 444.632,454.48 433.622,454.48L113.572,454.48C102.552,454.48 93.592,445.52 93.592,434.5L93.592,57.47C93.592,46.46 102.552,37.5 113.572,37.5L367.592,37.5L376.832,46.74L453.592,123.49Z" style="fill:rgb(217,249,248);fill-rule:nonzero;"/>
						<path d="M453.592,123.49L453.592,434.5C453.592,445.52 444.632,454.48 433.622,454.48L113.572,454.48C102.552,454.48 93.592,445.52 93.592,434.5L93.592,429.48L412.892,429.48C424.042,429.48 433.082,420.44 433.082,409.28L433.082,123.49L376.832,46.74L453.592,123.49Z" style="fill:rgb(193,234,244);fill-rule:nonzero;"/>
						<path d="M453.592,123.49L433.082,123.49L374.172,123.48C370.542,123.48 367.592,120.54 367.592,116.9L367.592,37.5C368.922,37.5 370.192,38.02 371.132,38.96L452.132,119.96C453.072,120.9 453.592,122.17 453.592,123.49Z" style="fill:rgb(174,201,214);fill-rule:nonzero;"/>
						<path d="M453.592,123.49L433.082,123.49L371.132,38.96L452.132,119.96C453.072,120.9 453.592,122.17 453.592,123.49Z" style="fill:rgb(143,178,188);fill-rule:nonzero;"/>
						<path d="M363.491,264.802L328.044,264.802C323.902,264.802 320.544,268.159 320.544,272.302C320.544,276.445 323.902,279.802 328.044,279.802L363.491,279.802C367.633,279.802 370.991,276.445 370.991,272.302C370.991,268.159 367.633,264.802 363.491,264.802ZM292.747,264.802L184.069,264.802C179.927,264.802 176.569,268.159 176.569,272.302C176.569,276.445 179.927,279.802 184.069,279.802L292.747,279.802C296.889,279.802 300.247,276.445 300.247,272.302C300.247,268.159 296.889,264.802 292.747,264.802ZM363.491,219.688L184.069,219.688C179.927,219.688 176.569,223.045 176.569,227.188C176.569,231.331 179.927,234.688 184.069,234.688L363.491,234.688C367.633,234.688 370.991,231.331 370.991,227.188C370.991,223.045 367.633,219.688 363.491,219.688ZM184.069,190.456L363.491,190.456C367.633,190.456 370.991,187.099 370.991,182.956C370.991,178.813 367.633,175.456 363.491,175.456L184.069,175.456C179.927,175.456 176.569,178.813 176.569,182.956C176.569,187.099 179.927,190.456 184.069,190.456Z" style="fill:rgb(174,201,214);fill-rule:nonzero;"/>
						<path d="M367.982,417.81C367.982,469.74 325.722,512 273.782,512C269.722,512 265.722,511.74 261.802,511.24L261.762,511.24C215.472,505.31 179.582,465.67 179.582,417.81C179.582,369.94 215.472,330.3 261.762,324.37L261.802,324.37C265.722,323.87 269.722,323.61 273.782,323.61C325.722,323.61 367.982,365.87 367.982,417.81Z" style="fill:rgb(79,232,188);fill-rule:nonzero;"/>
						<path d="M367.982,417.81C367.982,469.74 325.722,512 273.782,512C269.722,512 265.722,511.74 261.802,511.24C308.092,505.31 343.982,465.67 343.982,417.81C343.982,369.94 308.092,330.3 261.802,324.37C265.722,323.87 269.722,323.61 273.782,323.61C325.722,323.61 367.982,365.87 367.982,417.81Z" style="fill:rgb(23,198,165);fill-rule:nonzero;"/>
						<path d="M311.881,425.013C308.952,422.083 304.203,422.084 301.275,425.012L281.281,445.005L281.281,372.499C281.281,368.356 277.923,364.999 273.781,364.999C269.639,364.999 266.281,368.356 266.281,372.499L266.281,445.005L246.287,425.012C243.358,422.084 238.609,422.083 235.681,425.013C232.752,427.942 232.752,432.691 235.681,435.619L268.478,468.415C269.942,469.879 271.862,470.611 273.781,470.611C275.7,470.611 277.62,469.879 279.084,468.415L311.881,435.619C314.81,432.69 314.81,427.941 311.881,425.013Z" style="fill:white;fill-rule:nonzero;"/>
					</svg>
				@endcomponent
				@if(in_array(1, $obj))
					@component("components.buttons.button", ["buttonElement" => "a", "variant" => "transparent"])
						@slot("classEx") m-2 disabled @endslot
						@slot("attributeEx") id="move_file" href="#" alt="Mover" title="Mover" data-toggle="modal" data-target="#moveFilesModal" @endslot
						<svg width="24" height="24" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
							<path d="M488,104C488,86.339 473.661,72 456,72L248,72C230.339,72 216,86.339 216,104L216,264C216,281.661 230.339,296 248,296L456,296C473.661,296 488,281.661 488,264L488,104Z" style="fill:rgb(247,204,56);"/>
							<path d="M216,312C216,329.555 230.445,344 248,344L456,344C473.555,344 488,329.555 488,312L488,152C488,134.445 473.555,120 456,120L344,120L344,56C344,38.445 329.555,24 312,24L248,24C230.445,24 216,38.445 216,56L216,312Z" style="fill:rgb(251,220,68);fill-rule:nonzero;"/>
							<path d="M296,248C296,230.339 281.661,216 264,216L56,216C38.339,216 24,230.339 24,248L24,408C24,425.661 38.339,440 56,440L264,440C281.661,440 296,425.661 296,408L296,248Z" style="fill:rgb(247,204,56);"/>
							<path d="M24,456C24,473.555 38.445,488 56,488L264,488C281.555,488 296,473.555 296,456L296,296C296,278.445 281.555,264 264,264L152,264L152,200C152,182.445 137.555,168 120,168L56,168C38.445,168 24,182.445 24,200L24,456Z" style="fill:rgb(251,227,106);fill-rule:nonzero;"/>
							<path d="M408,344L352,296L352,320C304,320 248,348.654 248,384C248,384 304,368 352,368L352,392L408,344Z" style="fill:rgb(247,149,57);fill-rule:nonzero;"/>
						</svg>	
					@endcomponent
					@component("components.buttons.button", ["buttonElement" => "a", "variant" => "transparent"])
						@slot("classEx") m-2 disabled @endslot
						@slot("attributeEx") id="remove_file" href="#" alt="Eliminar archivo" title="Eliminar archivo" @endslot
						<svg width="24" height="24" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
							<path d="M397.65,148.394L397.65,420.314C397.65,443.072 379.2,461.521 356.443,461.521L73.143,461.521C50.385,461.521 31.936,443.072 31.936,420.314L31.936,41.207C31.936,18.45 50.385,0 73.143,0L249.507,0C254.191,0 258.765,1.063 262.906,3.056C266.023,4.554 393.349,132.249 394.862,135.567C396.683,139.557 397.65,143.926 397.65,148.394Z" style="fill:rgb(249,248,249);fill-rule:nonzero;"/>
							<path d="M397.65,148.398L397.65,166.518L290.181,166.518C257.782,166.518 231.42,140.125 231.42,107.695L231.42,0L249.51,0C253.985,0 258.362,0.972 262.358,2.801C265.684,4.323 393.42,132.351 394.938,135.733C396.709,139.677 397.65,143.988 397.65,148.398Z" style="fill:rgb(227,224,228);fill-rule:nonzero;"/>
							<path d="M104.048,461.521L73.143,461.521C50.386,461.521 31.936,443.071 31.936,420.314L31.936,41.207C31.936,18.451 50.386,0 73.143,0L104.048,0C81.292,0 62.841,18.451 62.841,41.207L62.841,420.314C62.841,443.071 81.292,461.521 104.048,461.521Z" style="fill:rgb(227,224,228);fill-rule:nonzero;"/>
							<path d="M218.51,339.96L91.076,339.96C86.809,339.96 83.35,336.5 83.35,332.233C83.35,327.966 86.809,324.507 91.076,324.507L218.51,324.507C222.777,324.507 226.236,327.966 226.236,332.233C226.236,336.5 222.777,339.96 218.51,339.96ZM319.776,278.149L89.016,278.149C84.749,278.149 81.29,274.69 81.29,270.423C81.29,266.156 84.749,262.696 89.016,262.696L319.776,262.696C324.043,262.696 327.503,266.156 327.503,270.423C327.503,274.69 324.043,278.149 319.776,278.149ZM319.776,216.338L89.016,216.338C84.749,216.338 81.29,212.879 81.29,208.612C81.29,204.345 84.749,200.885 89.016,200.885L319.776,200.885C324.043,200.885 327.503,204.345 327.503,208.612C327.503,212.879 324.043,216.338 319.776,216.338ZM218.51,154.527L91.076,154.527C86.809,154.527 83.35,151.068 83.35,146.801C83.35,142.534 86.809,139.074 91.076,139.074L218.51,139.074C222.777,139.074 226.236,142.534 226.236,146.801C226.236,151.068 222.777,154.527 218.51,154.527ZM394.879,135.613L290.181,135.613C274.801,135.613 262.325,123.107 262.325,107.695L262.325,2.802C265.663,4.306 268.743,6.428 271.381,9.076L388.626,126.568C391.252,129.205 393.375,132.275 394.879,135.613Z" style="fill:rgb(162,154,165);fill-rule:nonzero;"/>
							<ellipse cx="375.49" cy="407.437" rx="104.575" ry="104.563" style="fill:rgb(220,73,85);"/>
							<path d="M459.018,470.35C439.939,495.651 409.621,512 375.491,512C317.739,512 270.917,465.189 270.917,407.437C270.917,373.307 287.266,342.999 312.567,323.92C299.35,341.423 291.521,363.211 291.521,386.833C291.521,444.585 338.342,491.396 396.094,491.396C419.716,491.396 441.505,483.567 459.018,470.35Z" style="fill:rgb(196,36,48);fill-rule:nonzero;"/>
							<path d="M415.929,447.865C409.898,453.897 400.108,453.897 394.076,447.865L375.501,429.29L356.926,447.865C350.894,453.897 341.104,453.897 335.073,447.865C329.041,441.833 329.041,432.044 335.073,426.012L353.648,407.437L335.073,388.861C329.041,382.83 329.041,373.04 335.073,367.008C341.104,360.976 350.894,360.976 356.926,367.008L375.501,385.583L394.076,367.008C400.108,360.976 409.898,360.976 415.929,367.008C421.961,373.04 421.961,382.83 415.929,388.861L397.354,407.437L415.929,426.012C421.961,432.044 421.961,441.834 415.929,447.865Z" style="fill:rgb(249,248,249);fill-rule:nonzero;"/>
						</svg>
					@endcomponent
				@endif
			</div>
		</div>
		<div class="col-span-4 my-4 order-2 md:order-none">
			<div id="folder_container" class="bg-white rounded-lg mx-4 h-full border-2 border-gray-300"></div>
		</div>
		<div class="col-span-8 folder_files m-4 order-last md:order-none"></div>
	</div>
	@component("components.modals.modal", ["attributeEx" => "id=\"upload-files\"", "variant" => "small"])
		@slot('modalFooter')
			@component("components.buttons.button", ["variant" => "red", "classEx" => "exit", "attributeEx" => "data-dismiss=\"modal\""]) CERRAR @endcomponent
		@endslot
	@endcomponent
	<div class="loading row"></div>
	@component("components.modals.modal", ["attributeEx" => "id=\"moveFilesModal\"", "variant" => "small", "modalTitle" => "Mover archivos"])
		@slot('modalBody')
			<div id="folder_move"></div>
		@endslot
		@slot('modalFooter')
			@component("components.buttons.button", ["variant" => "success", "classEx" => "move-files-selected", "attributeEx" => "data-dismiss=\"modal\""]) MOVER @endcomponent
			@component("components.buttons.button", ["variant" => "red", "classEx" => "exit", "attributeEx" => "data-dismiss=\"modal\""]) CERRAR @endcomponent
		@endslot
	@endcomponent
@endsection

@section('scripts')
	<script src="{{ asset('js/jstree/jstree.min.js') }}"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.11.1/cr-1.5.4/fh-3.1.9/r-2.2.9/sc-2.0.5/datatables.min.js"></script>
	<script>
		$(function ()
		{
			$('#folder_container').jstree({
				'core' : {
					'data' : {
						'url' : '{{route("construction-processes.folder")}}',
						'data': function (node) {
							return { 'id' : node.id };
						}
					},
					'check_callback' : true,
					'multiple': false,
				},
				'sort' : function(a, b) {
					return this.get_type(a) === this.get_type(b) ? (this.get_text(a) > this.get_text(b) ? 1 : -1) : (this.get_type(a) >= this.get_type(b) ? 1 : -1);
				},
				'unique' : {
					'duplicate' : function (name, counter)
					{
						return name + '_' + counter;
					}
				},
				'plugins' : ['state','dnd','sort','types','unique']
			})
			.on('ready.jstree', function()
			{
				$(this).jstree('open_all');
			})
			.on('delete_node.jstree', function (e, data)
			{
				$.post('{{route("construction-processes.folder.delete")}}', { 'id' : data.node.id, '_token': $('meta[name="csrf-token"]').attr('content') })
					.fail(function () {
						data.instance.refresh();
					});
			})
			.on('create_node.jstree', function (e, data)
			{
				$.post('{{route("construction-processes.folder.create")}}', { 'type' : data.node.type, 'id' : data.node.parent, 'text' : data.node.text, '_token': $('meta[name="csrf-token"]').attr('content') })
					.done(function (d) {
						data.instance.set_id(data.node, d.id);
					})
					.fail(function () {
						data.instance.refresh();
					});
			})
			.on('rename_node.jstree', function (e, data) {
				$.post('{{route("construction-processes.folder.rename")}}', { 'id' : data.node.id, 'text' : data.text, '_token': $('meta[name="csrf-token"]').attr('content') })
					.done(function (d) {
						data.instance.set_id(data.node, d.id);
						data.instance.refresh();
					})
					.fail(function () {
						data.instance.refresh();
					});
			})
			.on('move_node.jstree', function (e, data) {
				$.post('{{route("construction-processes.folder.move")}}', { 'id' : data.node.id, 'parent' : data.parent, '_token': $('meta[name="csrf-token"]').attr('content') })
					.done(function (d) {
						data.instance.refresh();
					})
					.fail(function () {
						data.instance.refresh();
					});
			})
			.on('changed.jstree', function (e, data)
			{
				if(data && data.selected && data.selected.length)
				{
					if(data.node.parent=='#')
					{
						$('#remove_folder,#rename_folder').addClass('disabled');
						$('#new_folder').removeClass('disabled');
					}
					else
					{
						$('#new_folder,#remove_folder,#rename_folder').removeClass('disabled');
					}
					$('.folder_files').html('<div class="loader"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"><defs><filter id="ldio-0o6e52k4z8cg-filter" x="-100%" y="-100%" width="300%" height="300%" color-interpolation-filters="sRGB"><feGaussianBlur in="SourceGraphic" stdDeviation="3.6"></feGaussianBlur><feComponentTransfer result="cutoff"><feFuncA type="table" tableValues="0 0 0 0 0 0 1 1 1 1 1"></feFuncA></feComponentTransfer></filter></defs><g filter="url(#ldio-0o6e52k4z8cg-filter)"><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#e41982"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="6.666666666666666s" repeatCount="indefinite" begin="-0.15000000000000005s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="6.666666666666666s" repeatCount="indefinite" begin="0s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#9c108a"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="3.333333333333333s" repeatCount="indefinite" begin="-0.1285714285714286s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="3.333333333333333s" repeatCount="indefinite" begin="-0.021428571428571432s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#41a5e0"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="2.222222222222222s" repeatCount="indefinite" begin="-0.10714285714285716s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="2.222222222222222s" repeatCount="indefinite" begin="-0.042857142857142864s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#7ac13f"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="1.6666666666666665s" repeatCount="indefinite" begin="-0.08571428571428573s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="1.6666666666666665s" repeatCount="indefinite" begin="-0.0642857142857143s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#02a04c"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="1.333333333333333s" repeatCount="indefinite" begin="-0.0642857142857143s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="1.333333333333333s" repeatCount="indefinite" begin="-0.08571428571428573s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#f8cd5c"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="1.111111111111111s" repeatCount="indefinite" begin="-0.042857142857142864s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="1.111111111111111s" repeatCount="indefinite" begin="-0.10714285714285716s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#ee871e"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="0.9523809523809521s" repeatCount="indefinite" begin="-0.021428571428571432s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="0.9523809523809521s" repeatCount="indefinite" begin="-0.1285714285714286s"></animateTransform></g></g></g></svg>Cargando...</div>');
					$.post('{{route("construction-processes.folder.files")}}', { 'id' : data.node.id})
						.done(function(d)
						{
							$('.folder_files').html(d);
							$('.folder_files table').DataTable({
								"dom": 'frtp',
								language: {
									processing    : "Cargando...",
									search        : "Buscar:",
									loadingRecords: "Cargando...",
									zeroRecords   : "No se encontraron resultados",
									emptyTable    : "La carpeta está vacía",
									paginate      : {
										first   : "Inicio",
										previous: "Anterior",
										next    : "Siguiente",
										last    : "Fin"
									}
								},
								scrollY       : 400,
								scrollX       : true,
								fixedHeader   : true,
								"pageLength"  : 50,
								"lengthChange": false,
							});
						});
					$('#upload_file').removeClass('disabled');
				}
				else
				{
					$('#upload_file').addClass('disabled');
					$('#new_folder,#remove_folder,#rename_folder').addClass('disabled');
					$('.folder_files').html('');
				}
				$(this).jstree('open_all');
			});
			$('#folder_move').jstree({
				'core' : {
					'data' : {
						'url' : '{{route("construction-processes.folder")}}',
						'data' : function (node) {
							return { 'id' : node.id };
						}
					},
					'check_callback' : false,
					'multiple': false,
				}
			})
			.on('ready.jstree', function()
			{
				$(this).jstree('open_all');
			});
		});
		$(document).on('click','.delete-file',function(e)
		{
			e.preventDefault();
			href = $(this).attr('href');
			swal({
				title: "Eliminar",
				text: "¿Confirma que desea eliminar el archivo? Esta acción es irreversible",
				icon: "warning",
				buttons: ["Cancelar","OK"],
				dangerMode: true,
			})
			.then((willDelete) => {
				if (willDelete)
				{
					form = $('<form></form>').attr('action',href).attr('method','post').append('@csrf').append('@method("put")');
					$(document.body).append(form);
					form.submit();
				}
			});
		})
		.on('click',function()
		{
			$('.folder_files table tbody tr').removeClass('active');
			$('#download_file,#move_file,#remove_file').addClass('disabled');
		})
		.on('click','.folder_files table tbody tr',function(e)
		{
			e.stopPropagation();
			if (e.shiftKey)
			{
				var flag = false;
				var that = $(this);
				if($(this).siblings('tr').hasClass('active'))
				{
					$('.folder_files table tbody tr').each(function(i,v)
					{
						if((that.is($(this)) || $(this).hasClass('active')) && !flag)
						{
							flag = true;
						}
						else if((that.is($(this)) || $(this).hasClass('active')) && flag)
						{
							$(this).addClass('active');
							flag = false;
						}
						if(flag)
						{
							$(this).addClass('active');
						}
					});
				}
				else
				{
					$(this).siblings('tr').removeClass('active');
					$(this).addClass('active');
				}
			}
			else if(e.ctrlKey || e.metaKey)
			{
				$(this).toggleClass('active');
			}
			else
			{
				$(this).siblings('tr').removeClass('active');
				$(this).addClass('active');
			}
			if($('.folder_files table tbody tr.active').length > 0)
			{
				$('#download_file,#move_file,#remove_file').removeClass('disabled');
			}
			else
			{
				$('#download_file,#move_file,#remove_file').addClass('disabled');
			}
		})
		.on('click','#new_folder',function(e)
		{
			e.preventDefault();
			e.stopPropagation();
			if(!$(this).hasClass('disabled'))
			{
				var tree = $('#folder_container').jstree(true),
					obj = tree.get_node(tree.get_selected());
				tree.create_node(obj, { type : "default" }, "last", function (new_node)
				{
					setTimeout(function () { tree.edit(new_node);},0);
				});
			}
		})
		.on('click','#rename_folder',function(e)
		{
			e.preventDefault();
			e.stopPropagation();
			if(!$(this).hasClass('disabled'))
			{
				var tree = $('#folder_container').jstree(true),
					obj = tree.get_node(tree.get_selected());
				tree.edit(obj);
			}
		})
		.on('click','#remove_folder',function(e)
		{
			e.preventDefault();
			e.stopPropagation();
			if(!$(this).hasClass('disabled'))
			{
				var tree = $('#folder_container').jstree(true),
					obj = tree.get_node(tree.get_selected());
				swal({
					title: "Eliminar",
					text: "¿Confirma que desea eliminar la carpeta «"+obj.text+"» junto con todos sus archivos?",
					icon: "warning",
					buttons: ["Cancelar","OK"],
					dangerMode: true,
				})
				.then((willDelete) => {
					if (willDelete)
					{
						if(tree.is_selected(obj))
						{
							tree.delete_node(tree.get_selected());
						}
						else
						{
							tree.delete_node(obj);
						}
					}
				});
			}
		})
		.on('click','#download_file',function(e)
		{
			e.preventDefault();
			e.stopPropagation();
			var tree = $('#folder_container').jstree(true);
			ids = [];
			$('.folder_files table tbody tr.active').each(function(i,v)
			{
				ids.push($(this).attr('data-id'));
			});
			window.location ='/tools/construction-processes/download/'+tree.get_selected()+'/'+ids;
		})
		.on('click','#remove_file',function(e)
		{
			e.preventDefault();
			e.stopPropagation();
			if(!$(this).hasClass('disabled'))
			{
				swal({
					title: "Eliminar",
					text: "¿Confirma que desea eliminar los archivos seleccionados?",
					icon: "warning",
					buttons: ["Cancelar","OK"],
					dangerMode: true,
				})
				.then((willDelete) => {
					if (willDelete)
					{
						ids = [];
						$('.folder_files table tbody tr.active').each(function(i,v)
						{
							ids.push($(this).attr('data-id'));
						});
						$.post('{{route("construction-processes.delete")}}', { ids: ids, '_token': $('meta[name="csrf-token"]').attr('content') })
						.done(function (d) {
							swal({
								title: "Eliminar",
								text: "Archivos eliminados",
								icon: "success",
							});
							var tree = $('#folder_container').jstree(true);
							tree.refresh();
						})
						.fail(function ()
						{
							swal({
								title: "Error",
								text: "Ocurrió un error, por favor intente de nuevo.",
								icon: "error",
							});
							var tree = $('#folder_container').jstree(true);
							tree.refresh();
						});
					}
				});
				$('#remove_file').addClass('disabled');
				$('#download_file').addClass('disabled');
				$('#move_file').addClass('disabled');
			}
		})
		.on('click','#move_file',function(e)
		{
			e.preventDefault();
			e.stopPropagation();
			$('#folder_move').jstree(true).destroy();
			$('#folder_move').jstree({
				'core' : {
					'data' : {
						'url' : '{{route("construction-processes.folder")}}',
						'data' : function (node) {
							return { 'id' : node.id };
						}
					},
					'check_callback' : false,
					'multiple': false,
				}
			})
			.on('ready.jstree', function()
			{
				var tree = $('#folder_container').jstree(true),
					obj = tree.get_node(tree.get_selected());
				$(this).jstree('open_all');
				var new_tree = $(this).jstree(true);
				new_tree.disable_node(obj);
			});
		})
		.on('click','.swal-overlay,.swal-overlay *,.dataTables_filter,.folder_files table,.modal',function(e)
		{
			e.stopPropagation();
		})
		.on('dblclick','.folder_files table tbody tr.active',function()
		{
			var tree = $('#folder_container').jstree(true);
			ids = [];
			ids.push($(this).attr('data-id'));
			window.location ='/tools/construction-processes/download/'+tree.get_selected()+'/'+ids;
		})
		.on('click','.move-files-selected',function(e)
		{
			var tree = $('#folder_move').jstree(true);
			if(tree.get_selected() != '')
			{
				ids = [];
				$('.folder_files table tbody tr.active').each(function(i,v)
				{
					ids.push($(this).attr('data-id'));
				});
				$.post('{{route("construction-processes.move")}}', { id: tree.get_selected(), ids: ids,  '_token': $('meta[name="csrf-token"]').attr('content') })
				.done(function (d)
				{
					$('#moveFilesModal').fadeOut();
					swal('Archivos','¡Éxito! Archivo(s) movido(s) exitosamente','success');
					var main_tree = $('#folder_container').jstree(true);
						main_tree.refresh();
				})
				.fail(function ()
				{
					swal({
						title: "Error",
						text: "Ocurrió un error, por favor intente de nuevo.",
						icon: "error",
					});
				});
			}
			else
			{
				swal('Destino','¡Alerta! Debe seleccionar una carpeta destino para continuar','warning');
			}
		})
		.on('click','#upload_file',function(e)
		{
			e.preventDefault();
			e.stopPropagation();
			$('#upload_files').click();
		})
		.on('change','#upload_files',function(e)
		{
			var tree = $('#folder_container').jstree(true),
				folder = tree.get_selected();
			$('.modal-body').html('');
			$(this).each(function(i,field)
			{
				for(var i = 0; i < field.files.length; i++)
				{
					const file = field.files[i];
					form       = new FormData();
					form.append('file',file);
					form.append('folder',folder);
					
					const id        = Date.now() + '' + i;
					const file_name = file.name;
					$.ajax(
					{
						url			: '{{route("construction-processes.upload")}}',
						type		: "POST",
						data		: form,
						contentType	: false,
						cache		: false,
						processData	: false,
						xhr			: function()
						{
							xhr = $.ajaxSettings.xhr();
							row = $('<div class="col-12 progress-bar-container"><div class="progress-bar" id="'+id+'"></div></div>');
							$('#upload-files').modal('show');
							$('.modal-body').append(row);
							xhr.upload.onprogress = function(evt)
							{
								load = (evt.loaded/evt.total*100).toFixed(2)+'%';
								$('#'+id).css({width:load}).html(load);
							};
							xhr.upload.onload = function()
							{
								$('#'+id).css({width:'100%'}).html('Procesando archivo «'+file_name+'»...');
							};
							return xhr;
						},
						success: function(r)
						{
							if(r.error=='DONE')
							{
								$('.folder_files').html('<div class="loader"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"><defs><filter id="ldio-0o6e52k4z8cg-filter" x="-100%" y="-100%" width="300%" height="300%" color-interpolation-filters="sRGB"><feGaussianBlur in="SourceGraphic" stdDeviation="3.6"></feGaussianBlur><feComponentTransfer result="cutoff"><feFuncA type="table" tableValues="0 0 0 0 0 0 1 1 1 1 1"></feFuncA></feComponentTransfer></filter></defs><g filter="url(#ldio-0o6e52k4z8cg-filter)"><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#e41982"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="6.666666666666666s" repeatCount="indefinite" begin="-0.15000000000000005s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="6.666666666666666s" repeatCount="indefinite" begin="0s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#9c108a"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="3.333333333333333s" repeatCount="indefinite" begin="-0.1285714285714286s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="3.333333333333333s" repeatCount="indefinite" begin="-0.021428571428571432s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#41a5e0"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="2.222222222222222s" repeatCount="indefinite" begin="-0.10714285714285716s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="2.222222222222222s" repeatCount="indefinite" begin="-0.042857142857142864s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#7ac13f"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="1.6666666666666665s" repeatCount="indefinite" begin="-0.08571428571428573s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="1.6666666666666665s" repeatCount="indefinite" begin="-0.0642857142857143s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#02a04c"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="1.333333333333333s" repeatCount="indefinite" begin="-0.0642857142857143s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="1.333333333333333s" repeatCount="indefinite" begin="-0.08571428571428573s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#f8cd5c"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="1.111111111111111s" repeatCount="indefinite" begin="-0.042857142857142864s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="1.111111111111111s" repeatCount="indefinite" begin="-0.10714285714285716s"></animateTransform></g></g><g transform="translate(50 50)"><g><circle cx="16" cy="0" r="5" fill="#ee871e"><animate attributeName="r" keyTimes="0;0.5;1" values="5.3999999999999995;12.6;5.3999999999999995" dur="0.9523809523809521s" repeatCount="indefinite" begin="-0.021428571428571432s"></animate></circle><animateTransform attributeName="transform" type="rotate" keyTimes="0;1" values="0;360" dur="0.9523809523809521s" repeatCount="indefinite" begin="-0.1285714285714286s"></animateTransform></g></g></g></svg>Cargando...</div>');
								$.post('{{route("construction-processes.folder.files")}}', { 'id' : tree.get_selected(), '_token': $('meta[name="csrf-token"]').attr('content') })
								.done(function(d)
								{
									$('.folder_files').html(d);
									$('.folder_files table').DataTable({
										"dom": 'frtp',
										language: {
											processing    : "Cargando...",
											search        : "Buscar:",
											loadingRecords: "Cargando...",
											zeroRecords   : "No se encontraron resultados",
											emptyTable    : "La carpeta está vacía",
											paginate      : {
												first   : "Inicio",
												previous: "Anterior",
												next    : "Siguiente",
												last    : "Fin"
											}
										},
										scrollY       : 400,
										scrollX       : true,
										fixedHeader   : true,
										"pageLength"  : 50,
										"lengthChange": false,
									});
								});
								$('#'+id).addClass('success').html('¡Archivo «'+file_name+'» procesado exitosamente!');
							}
							else
							{
								swal('Error','Ocurrió un error durante el procesamiento del archivo «'+file_name+'», por favor verifique su archivo e intente de nuevo.','error');
								$('#'+id).css({width:'100%'}).addClass('error').html('Error, el archivo «'+file_name+'» no pudo ser procesado.');
							}
							$('#upload_files').val('');
						},
						error: function(xhr)
						{
							if(xhr.readyState == 0)
							{
								swal('Error','Ocurrió un error de conexión, por favor verifique que su archivo «'+file_name+'» se haya procesado, de lo contario intente de nuevo.','error');
							}
							else
							{
								swal('Error','Ocurrió un error durante la carga del archivo «'+file_name+'», por favor intente de nuevo.','error');
							}
							$('#'+id).css({width:'100%'}).addClass('error').html('Error, el archivo «'+file_name+'» no pudo ser procesado.');
							$('#upload_files').val('');
						}
					})
				}
			});
		});
	</script>
@endsection
