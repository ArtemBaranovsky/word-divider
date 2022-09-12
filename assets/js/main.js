window.onload = function() {
	$('#sendcode').on('click', function (event) {
		$.ajax({
			method: 'post',
			url:  window.location.href + "index.php",
			async: true,
			data: {
				send_to_phone: $('input[name="confirm"]')[0].value,
				code: $('#sendcode').data('code')
			}
		}).done(function (data) {
			console.log(data)
			// TODO: pass Nexmo response to check answer
			$('#sendcode').attr("disabled", "disabled");
		});
	});

	function checkForm(form)
	{
		if(!form.captchacode.value.match(/^\d{5}$/)) {
			alert('Please enter the CAPTCHA digits in the box provided');
			form.captchacode.focus();

			return false;
		}

		return true;
	}
}

