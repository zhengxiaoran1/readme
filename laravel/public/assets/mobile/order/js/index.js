$(function() {
	var Accordion = function(el, multiple) {
		this.el = el || {};
		this.multiple = multiple || false;
		var have =false
		var links = this.el.find('.link');

		$('body').on('click','.link',function(e){
			var $el = e.target;
			$this = $(this),
			$next = $this.next();
			$next.slideToggle();
			$this.parent().toggleClass('open');
			have =true
			if (have) {
				$('body').find('.submenu').not($next).slideUp().parent().removeClass('open');
			};
		})
	}

	Accordion.prototype.dropdown = function(e) {
		var $el = e.data.el;
		console.log($el)
			$this = $(this),
			$next = $this.next();
		$next.slideToggle();
		$this.parent().toggleClass('open');


		if (!e.data.multiple) {
			$el.find('.submenu').not($next).slideUp().parent().removeClass('open');
		};
	}

	var accordion = new Accordion($('#accordion'), false);
});