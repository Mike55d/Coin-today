@extends('layouts.master')
@section('content')
<div class="row">
	<div class="col-md-8 offset-md-2">
		<div class="container mt-5">
			<div class="card">
				<div class="card-header bg-dark">
					<h1 class="text-center text-white">Coin Today</h1>
				</div>
				<div class="card-body">
					<div class="row mt-3">
						<form name="currencyForm" id="currencyForm">
							<div class="row">
								<div class="col-md-4">
									<input type="number" name="amount" placeholder="00.00" class="form-control">
								</div>
								<div class="col-md-4">
									<select class="dropdown-select-2" name="from" style="width: 100%;">
										@foreach ($currencies as $code => $country)
										<option value="{{$code}}-{{$country}}"> {{$code}} - {{$country}}</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-4">
									<select class="dropdown-select-2" name="to" style="width: 100%;">
										@foreach ($currencies as $code => $country)
										<option value="{{$code}}-{{$country}}"> {{$code}} - {{$country}}</option>
										@endforeach
									</select>
								</div>
							</div>
						</form>
						<div class="col-md-12 text-center mt-4">
							<button class="btn btn-success w-50 btn-convert" onclick="getFormData()">
								<span class="spinner-border spinner-border-sm spinner hide" role="status" aria-hidden="true"></span>
								<span class="text-btn">Convert</span>
							</button>
						</div>
						<div class="col-md-12 mt-5">
							<h6 class="amount"></h6>
							<h2 class="result h1 text-primary"></h2>
							<h6 class="oneFrom"></h6>
							<h6 class="oneTo"></h6>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
	function formatState(state) {
		if (!state.id) {
			return state.text;
		}
		const countryCode = state.text.split('-')[0].replaceAll(' ', '').slice(0, -1);
		var baseUrl = "https://flagsapi.com";
		var $state = $(
			`<span><img src="${baseUrl}/${countryCode}/flat/64.png" class="img-flag"/>${state.text} </span>`
		);
		return $state;
	};

	function getFormData(event) {
		data = {};
		input_serialized = $('#currencyForm').serializeArray();
		input_serialized.forEach(field => {
			data[field.name] = field.name == 'from' ||
				field.name == 'to' ? field.value.split('-')[0] : field.value;
		})
		if (!data.amount || !data.from || !data.to) return;
		const countryFrom = input_serialized[1].value.split('-')[1];
		const countryTo = input_serialized[2].value.split('-')[1];
		const route = "{{route('getCurrency')}}";
		$('.spinner').removeClass('hide');
		$('.text-btn').text('Loading...');
		$('.btn-convert').prop('disabled', true);
		$.ajax({
			url: route,
			method: "GET",
			data,
		}).done((res) => {
			$('.spinner').addClass('hide');
			$('.text-btn').text('Convert');
			$('.btn-convert').prop('disabled', false);
			const result = res.result;
			const oneFrom = result / data.amount; // one Usd to VEF
			const oneTo = 1 / oneFrom; // one VEF to usd
			$('.amount').text(`${data.amount} ${countryFrom}s = `);
			$('.result').text(`${result.toFixed(4)} ${countryTo}s`);
			$('.oneFrom').text(`1 ${data.from} = ${oneFrom.toFixed(4)} ${data.to}`);
			$('.oneTo').text(`1 ${data.to} = ${oneTo.toFixed(4)} ${data.from}`);
		})

	}

	$(".dropdown-select-2").select2({
		templateResult: formatState,
		templateSelection: formatState,
		placeholder: "Select a state",
		allowClear: true
	}).val(null).trigger('change');
</script>

@stop