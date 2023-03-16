(function() {
	const $saveBtn = document.querySelector('[name="naswp_save"]');

	const toggleBtn = () => {
		const oldTitle = $saveBtn.innerText;
		const newTitle = $saveBtn.dataset.loadingCaption;
		$saveBtn.innerText = newTitle;
		$saveBtn.dataset.loadingCaption = oldTitle;
		$saveBtn.disabled = !$saveBtn.disabled;
	};

	$saveBtn.addEventListener('click', e => {
		toggleBtn();

		const data = new FormData();
		data.append('action', 'naswp_visitors_save');
		data.append('id', naswp_visitors.ID);
		data.append('naswp_reset', document.querySelector('[name="naswp_reset"]').checked ? 1 : 0);
		data.append('naswp_total', document.querySelector('[name="naswp_total"]').value);

		fetch(naswp_visitors.ajax_url, {
			method: "POST",
			credentials: 'same-origin',
			body: data
		})
		.then(response => response.json())
		.then(data => {
			const $table = document.getElementById('naswpVisitorsTable');
			$table.innerHTML = data.table;

			toggleBtn();
		})
		.catch(error => console.error(error));
	});
})();