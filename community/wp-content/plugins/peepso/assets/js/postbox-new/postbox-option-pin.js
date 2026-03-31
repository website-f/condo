import $ from 'jquery';
import { hooks, observer } from 'peepso';
import { is_admin as IS_ADMIN, datetime as datetimeData } from 'peepsodata';
import { DateSelector, TimeSelector } from '../datetime';

const MONTH_NAMES = datetimeData && datetimeData.text.monthNames;
const TEXT_AM = datetimeData && datetimeData.text.am;
const TEXT_PM = datetimeData && datetimeData.text.pm;

class PostboxPin extends peepso.class('PostboxOption') {
	constructor(postbox) {
		super(postbox);

		this.pin = null;

		this.postbox = postbox;
		this.$postbox = postbox.$postbox;
		this.$toggle = this.$postbox.find('[data-ps=option][data-id=pin]');
		this.$dropdown = this.$postbox.find('[data-ps=dropdown][data-id=pin]');
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

		this.minuteInterval = +this.$minute.data('interval') || 15;
		this.dateSelector = null;
		this.timeSelector = null;

		// Only admin should be able to pin a regular post. Additionally,
		// only user with `can_pin_posts` flag should be able to pin a group post.
		this.is_admin = observer.applyFilters('postbox_can_pin', +IS_ADMIN);
		this.is_admin ? this.$toggle.show() : this.$toggle.hide();

		this.$toggle.on('click', () => this.toggle());
		this.$dropdown.on('click', '[data-value]', e => this.onItemClick(e));
		this.$cancel.on('click', e => this.onCancelClick(e));
		this.$done.on('click', e => this.onDoneClick(e));

		hooks.addFilter('postbox_data', 'pin', (...args) => this.onPostboxData(...args));
		hooks.addFilter('postbox_is_empty', (...args) => this.onFilterIsEmpty(...args));
		hooks.addAction('postbox_reset', 'pin', (...args) => this.onPostboxReset(...args));
	}

	set(value) {
		if (value === 'no') {
			this.pin = null;
			this.$radio.get(0).checked = true;
			this.$datetime.hide();
			this.$toggle.removeClass('pso-postbox__option--active');
			hooks.doAction('postbox_pin_reset', this);
		} else if (value === 'indefinitely') {
			this.pin = 1;
			this.$radio.get(1).checked = true;
			this.$datetime.hide();
			this.$toggle.addClass('pso-postbox__option--active');
			hooks.doAction('postbox_pin_set', this);
		} else if (value === 'until') {
			this.pin = 'until';
			this.$radio.get(2).checked = true;
			this.$datetime.show();
			this.$toggle.addClass('pso-postbox__option--active');
			this.initDateTime();
			hooks.doAction('postbox_pin_set', this);
		}

		this.postbox.$textarea.trigger('input');
	}

	onPostboxData(data, postbox) {
		if (postbox === this.postbox) {
			if (this.pin === 'until') {
				if (this.dateSelector && this.timeSelector) {
					data.pin = `${this.dateSelector.getDate()} ${this.timeSelector.getTime()}`;
				}
			} else {
				data.pin = this.pin || undefined;
			}
		}

		return data;
	}

	onFilterIsEmpty(empty, postbox, data) {
		if (postbox === this.postbox) {
			if (data.pin) empty = false;
		}

		return empty;
	}

	onPostboxReset(postbox) {
		if (postbox === this.postbox) {
			this.set('no');
		}
	}

	onItemClick(e) {
		e.preventDefault();
		e.stopPropagation();

		let data = $(e.currentTarget).data();

		this.set(data.value);
		this.toggle(data.value === 'until');
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

		let onSelect = () => {
			let now = new Date(),
				selectedDate,
				selectedTime,
				selectedDateTime;

			selectedDate = this.dateSelector.getDate().split('-');
			selectedTime = this.timeSelector.getTime().split(':');
			selectedDateTime = new Date(
				+selectedDate[0],
				+selectedDate[1] - 1,
				+selectedDate[2],
				+selectedTime[0],
				+selectedTime[1]
			);

			// Users should only be able to post 1 hour in the future or more.
			// It should also conform minutes with the minute interval.
			now.setHours(
				now.getHours() + 1,
				now.getMinutes() + (this.minuteInterval - (now.getMinutes() % this.minuteInterval)),
				0
			);

			if (selectedDateTime < now) {
				this.timeSelector.setTime(`${now.getHours()}:${now.getMinutes()}`);
			}
		};

		let monthNames = MONTH_NAMES,
			minDate = new Date(),
			maxDate = new Date(),
			dateOpts = { monthNames, minDate, maxDate, onSelect },
			timeOpts = { step: this.minuteInterval, am: TEXT_AM, pm: TEXT_PM, onSelect };

		// Set maximum date to year 2035.
		maxDate.setFullYear(Math.max(2035, maxDate.getFullYear() + 1));

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

hooks.addAction('postbox_init', 'pin', postbox => new PostboxPin(postbox));
