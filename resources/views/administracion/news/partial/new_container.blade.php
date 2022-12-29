<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="x-apple-disable-message-reformatting">
	<title></title>
	<style>
		table, td, div, h1, p {font-family: Arial, sans-serif;}
	</style>
</head>
<body style="margin:0;padding:0;">
	<table role="presentation" style="width:100%;border-collapse:separate;border:0;border-spacing:0;background:#ffffff;">
		<tr>
			<td align="center" style="padding:0;">
				<table role="presentation" style="width:800px;border-collapse:separate;border:1px solid #cccccc;border-spacing:0;text-align:left;">
					<tr>
						<td style="padding:36px 30px 42px 30px; background: white;">
							<table role="presentation" style="width:100%;border-collapse:separate;border:0;border-spacing:0;">
								<tr>
									<td style="padding:0 0 36px 0;color:#153643;">
										<h1 style="font-size:24px;margin:0 0 20px 0;font-family:Arial,sans-serif;color: black;">Alerta de Noticias</h1>
										<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;color: black;"> {{ $name }}, te traemos las últimas noticias sobre "{{ $description }}" </p>
									</td>
								</tr>
								<tr>
									<td style="padding:0;">
										<table role="presentation" style="width:100%;border-collapse:separate;border:0;border-spacing:20px;">
											@php
												$count = 0;
												$td = 0;
											@endphp
											@foreach ($resultNews as $key => $new) 	
												@if($count == 0)
													<tr>
												@endif
													@if($td == 0)
														@php
															$td = 1;
														@endphp
														<td style="width:260px;padding:0.5em;vertical-align:top;color:#153643;border-right: 0.5px solid gray;border-top: none;border-bottom: none;border-left: none;">
													@else
														@php
															$td = 0;
														@endphp
														<td style="width:260px;padding:0.5em;vertical-align:top;color:#153643;border-right: none;border-top: none;border-bottom: none;border-left: none;">
													@endif
														<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;color: black;font-style: italic; text-align: center;">
															"{{ $new['title'] }}"
														</p>
														<p style="text-align: center;">
															@if($new['media'] != "")
																<img style="width: 85%;border-radius: 5px;" src="{{ $new['media'] }}">
															@endif
														</p>
														<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;color: black; text-align: center;">
															<a href="{{ $new['link'] }}" style="color: #ffffff;text-decoration: none;background: #137ca8;padding: 0.5em;border-radius: 5px;">Ver más</a>
														</p>
													</td>
													@php
														$count++;
													@endphp
												@if($count == 2)
													</tr>
													@php
														$count = 0;
													@endphp
												@endif
											@endforeach
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td style="padding:30px;background:#ee4c50;">
							<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">
								<tr>
									<td style="padding:0;width:50%;" align="left">
										<p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">
											Favor de no responder este correo. <br/>
										</p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
