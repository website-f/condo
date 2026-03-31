peepso.class('PostboxOption', function (name, peepso, $) {
	let dropdownCounter = 0;

	return class {
		constructor() {
			this.eventId = `postbox-${++dropdownCounter}`;
			this.$toggle = null;
			this.$dropdown = null;
		}

		toggle(show) {
			if ('undefined' === typeof show) {
				show = this.$dropdown.is(':hidden');
			}

			show ? this.show() : this.hide();
		}

		show() {
			this.$dropdown.show();

			setTimeout(() => {
				$(document)
					.off(`mouseup.${this.eventId}`)
					.on(`mouseup.${this.eventId}`, e => {
						let $container = this.$toggle.add(this.$dropdown);
						if ($container.filter(e.target).length) return;
						if ($container.find(e.target).length) return;
						this.hide();
					});
			}, 1);
		}

		hide() {
			this.$dropdown.hide();
			$(document).off(`mouseup.${this.eventId}`);
		}
	};
});
