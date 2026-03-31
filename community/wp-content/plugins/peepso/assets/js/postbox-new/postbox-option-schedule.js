import $ from 'jquery';
import { hooks } from 'peepso';
import { datetime as datetimeData, btn_schedule_text as btnLabel } from 'peepsodata';
import { DateSelector, TimeSelector } from '../datetime';

const MONTH_NAMES = datetimeData && datetimeData.text.monthNames;
const TEXT_AM = datetimeData && datetimeData.text.am;
const TEXT_PM = datetimeData && datetimeData.text.pm;

class PostboxSchedule extends peepso.class('PostboxOption') {
	constructor(postbox) {
		super(postbox);

		this.schedule = null;

		this.postbox = postbox;
		this.$postbox = postbox.$postbox;
		this.$toggle = this.$postbox.find('[data-ps=option][data-id=schedule]');
		this.$dropdown = this.$postbox.find('[data-ps=dropdown][data-id=schedule]');
		this.$cancel = this.$dropdown.find('[data-ps=btn-cancel]');
		this.$radio = this.$dropdown.find('[type=radio]');

		// Date & time selector.
		this.$datetime = this.$dropdown.find('.ps-js-datetime');
		this.$date = this.$datetime.find('.ps-js-date-dd');
		this.$month = this.$datetime.find('.ps-js-date-mm');
		this.$year = this.$datetime.find('.ps-js-date-yy');
		this.$hour = this.$datetime.find('.ps-js-time-hh');
		this.$minute = this.$datetime.find('.ps-js-time-mm');
		this.$ampm = this.$datetime.find('.ps-js-time-ampm');
		this.$done = this.$datetime.find('.ps-js-done');

		this.minuteInterval = +this.$minute.data('interval') || 5;
		this.dateSelector = null;
		this.timeSelector = null;

		this.$toggle.on('click', () => this.toggle());
		this.$dropdown.on('click', '[data-value]', e => this.onItemClick(e));
		this.$cancel.on('click', e => this.onCancelClick(e));
		this.$done.on('click', e => this.onDoneClick(e));

		hooks.addFilter('postbox_data', 'schedule', (...args) => this.onPostboxData(...args));
		hooks.addFilter('postbox_is_empty', (...args) => this.onFilterIsEmpty(...args));
		hooks.addAction('postbox_reset', 'schedule', (...args) => this.onPostboxReset(...args));
	}

	set(value) {
		let $submit = this.postbox.$btnSubmit;

		if (value === 'now') {
			this.schedule = null;
			this.$radio.get(0).checked = true;
			this.$datetime.hide();
			this.$toggle.removeClass('pso-postbox__option--active');
			$submit.data('text-orig') && $submit.html($submit.data('text-orig'));
			hooks.doAction('postbox_schedule_reset', this);
		} else if (value === 'future') {
			this.schedule = 'future';
			this.$radio.get(1).checked = true;
			this.$datetime.show();
			this.$toggle.addClass('pso-postbox__option--active');
			$submit.data('text-orig') || $submit.data('text-orig', $submit.html());
			$submit.html(btnLabel);
			this.initDateTime();
			hooks.doAction('postbox_schedule_set', this);
		}

		this.postbox.$textarea.trigger('input');
	}

	onPostboxData(data, postbox) {
		if (postbox === this.postbox) {
			if (this.schedule === 'future') {
				if (this.dateSelector && this.timeSelector) {
					let date = `${this.dateSelector.getDate()} ${this.timeSelector.getTime()}`;
					let selectedDate = this.dateSelector.getDate().split('-');
					let selectedTime = this.timeSelector.getTime().split(':');
					let selectedDateTime = new Date(
						+selectedDate[0],
						+selectedDate[1] - 1,
						+selectedDate[2],
						+selectedTime[0],
						+selectedTime[1]
					);

					if (selectedDateTime.getTime() > new Date().getTime()) {
						data.future = date;
					} else {
						data.date = date;
					}
				}
			} else {
				data.future = undefined;
				data.date = undefined;
			}
		}

		return data;
	}

	onFilterIsEmpty(empty, postbox, data) {
		if (postbox === this.postbox) {
			if (data.schedule) empty = false;
		}

		return empty;
	}

	onPostboxReset(postbox) {
		if (postbox === this.postbox) {
			this.schedule = null;
			this.$radio.get(0).checked = true;
			this.$datetime.hide();
			this.$toggle.removeClass('pso-postbox__option--active');

			let $submit = this.postbox.$btnSubmit;
			$submit.data('text-orig') && $submit.html($submit.data('text-orig'));
		}
	}

	onItemClick(e) {
		e.preventDefault();
		e.stopPropagation();

		let data = $(e.currentTarget).data();

		this.set(data.value);
		this.toggle(data.value === 'future');
	}

	onDoneClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.toggle(false);
	}

	onCancelClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.toggle(false);
	}

	/**
	 * Initialize date and time selectors.
	 */
	initDateTime() {
		if (this.dateSelector || this.timeSelector) {
			return;
		}

		let lastSelectedDateTime;
		let onSelect = () => {
			let selectedDate = this.dateSelector.getDate().split('-');
			let selectedTime = this.timeSelector.getTime().split(':');
			let selectedDateTime = new Date(
				+selectedDate[0],
				+selectedDate[1] - 1,
				+selectedDate[2],
				+selectedTime[0],
				+selectedTime[1]
			);

			// If today is selected, set the default time to next hour.
			const now = new Date();
			const getYmd = date => `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;
			if (getYmd(selectedDateTime) === getYmd(now)) {
				if (!lastSelectedDateTime || getYmd(lastSelectedDateTime) !== getYmd(now)) {
					// It should also conform minutes with the minute interval.
					now.setHours(
						now.getHours() + 1,
						now.getMinutes() +
							(this.minuteInterval - (now.getMinutes() % this.minuteInterval)),
						0
					);

					this.timeSelector.setTime(`${now.getHours()}:${now.getMinutes()}`);
				}
			}

			lastSelectedDateTime = selectedDateTime;
		};

		let monthNames = MONTH_NAMES,
			minDate = new Date(),
			maxDate = new Date(),
			dateOpts = { monthNames, minDate, maxDate, onSelect },
			timeOpts = { step: this.minuteInterval, am: TEXT_AM, pm: TEXT_PM, onSelect };

		// Set year of the maximum and minimum date.
		minDate.setFullYear(minDate.getFullYear() - 10);
		maxDate.setFullYear(maxDate.getFullYear() + 10);

		this.dateSelector = new DateSelector(
			this.$year[0],
			this.$month[0],
			this.$date[0],
			dateOpts
		);

		this.timeSelector = new TimeSelector(
			this.$hour[0],
			this.$minute[0],
			this.$ampm.length ? this.$ampm[0] : timeOpts,
			this.$ampm.length ? timeOpts : undefined
		);

		this.resetDateTime();
	}

	/**
	 * Reset selected date and time to the default value.
	 */
	resetDateTime() {
		let defaultDate = new Date();

		// Set default date to the next day (tomorrow).
		defaultDate.setDate(defaultDate.getDate() + 1);

		this.setDateTime(defaultDate);
	}

	/**
	 * Set the selected date and time to a provided value.
	 *
	 * @param {Date} date
	 */
	setDateTime(date) {
		this.initDateTime();

		let dateString = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`,
			timeString = `${date.getHours()}:${date.getMinutes()}`;

		this.dateSelector.setDate(dateString);
		this.timeSelector.setTime(timeString);
	}
}

hooks.addAction('postbox_init', 'schedule', postbox => new PostboxSchedule(postbox));
