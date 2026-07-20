'use strict';

function clearFileInput(input) {
	const $input = $(input);
	const dropify = $input.data('dropify');

	if (dropify) {
		dropify.resetPreview();
		dropify.clearElement();
		return;
	}

	$input.val('');
}

async function isValidImageSignature(file) {
	const bytes = new Uint8Array(await file.slice(0, 12).arrayBuffer());
	const isPng = bytes.length >= 8
		&& bytes[0] === 0x89 && bytes[1] === 0x50 && bytes[2] === 0x4E && bytes[3] === 0x47
		&& bytes[4] === 0x0D && bytes[5] === 0x0A && bytes[6] === 0x1A && bytes[7] === 0x0A;
	const isJpg = bytes.length >= 3
		&& bytes[0] === 0xFF && bytes[1] === 0xD8 && bytes[2] === 0xFF;

	return isPng || isJpg;
}

async function canDecodeImage(file) {
	return new Promise((resolve) => {
		const url = URL.createObjectURL(file);
		const img = new Image();

		img.onload = () => {
			URL.revokeObjectURL(url);
			resolve(img.naturalWidth > 0 && img.naturalHeight > 0);
		};

		img.onerror = () => {
			URL.revokeObjectURL(url);
			resolve(false);
		};

		img.src = url;
	});
}

async function validateImageInput(input, options = {}) {
	const settings = $.extend({
		alert: true,
		clearOnError: true,
		title: 'File tidak valid',
		text: 'File terdeteksi corrupt. Silakan upload ulang file yang benar.',
	}, options);

	const file = input?.files?.[0];
	if (!file) {
		return true;
	}

	try {
		const valid = await isValidImageSignature(file) && await canDecodeImage(file);
		if (!valid) {
			if (settings.clearOnError) {
				clearFileInput(input);
			}

			if (settings.alert && typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'error',
					title: settings.title,
					text: settings.text,
				});
			}

			return false;
		}

		return true;
	} catch (error) {
		if (settings.clearOnError) {
			clearFileInput(input);
		}

		if (settings.alert && typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'error',
				title: 'Gagal membaca file',
				text: 'Terjadi kendala saat validasi file. Silakan pilih file lain.',
			});
		}

		return false;
	}
}

async function validateImageInputs(target, options = {}) {
	const inputs = $(target).get();

	for (const input of inputs) {
		const valid = await validateImageInput(input, options);
		if (!valid) {
			return false;
		}
	}

	return true;
}
