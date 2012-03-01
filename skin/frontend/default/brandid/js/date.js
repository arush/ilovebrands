(function($) {
  $.date = {
    strftime: function(date, format) { 
      var day = date.getDay(),
          month = date.getMonth(),
          hours  = date.getHours(),
          minutes = date.getMinutes(),
          normalize = function(str) {
            if(Number(str) == 0)
              return '12';
            return str;
          };
      
      return format.replace(/\%([aAbBcdHImMpSwyY])/g, function(part) {
        switch(part[1]) {
          case 'a': return "Sun Mon Tue Wed Thu Fri Sat".split(' ')[day];
          case 'A': return "Sunday Monday Tuesday Wednesday Thursday Friday Saturday".split(' ')[day];
          case 'b': return "Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec".split(' ')[month];
          case 'B': return "January February March April May June July August September October November December".split(' ')[month];
          case 'c': return date.toString();
          case 'd': return zeropad(date.getDate());
          case 'H': return zeropad(hours);
          case 'I': return normalize(zeropad((hours + 12) % 12));
          case 'm': return normalize(zeropad(month + 1));
          case 'M': return zeropad(minutes);
          case 'p': return hours > 11 ? 'PM' : 'AM';
          case 'S': return pad(date.getSeconds());
          case 'w': return day + 1;
          case 'y': return pad(date.getFullYear() % 100);
          case 'Y': return date.getFullYear().toString();
        } 
      })
    },
    
    formats: {
      time: "%I:%M %p",
    	day:  "%B %d",
    	compact: "%b %d",
    	dayName: "%A"
    },
    
    /**
     * 
     */
    differenceFromNow: function(to) {
      var seconds = Math.ceil((new Date().getTime() - to.getTime()) / 1000);
      var days    = zeropad(Math.floor(seconds / 86400, 2));
      seconds     = Math.floor(seconds % 86400);
      var hours   = zeropad(Math.floor(seconds / 3600), 2);
      seconds     = Math.floor(seconds % 3600);
      var minutes = zeropad(Math.floor(seconds / 60), 2);
      seconds = zeropad((seconds % 60), 2);
      return [days, hours, minutes, seconds];
    },
    
    timeSince: function(to) {
      var seconds = Math.ceil((to.getTime() - new Date().getTime()) / 1000);
      var days    = zeropad(Math.floor(seconds / 86400, 2));
      seconds     = Math.floor(seconds % 86400);
      var hours   = zeropad(Math.floor(seconds / 3600), 2);
      seconds     = Math.floor(seconds % 3600);
      var minutes = zeropad(Math.floor(seconds / 60), 2);
      seconds = zeropad((seconds % 60), 2);
      return [days, hours, minutes, seconds];
    },
    
    parseUTC: function(value) {
      var localDate = new Date(value);
      var utcSeconds = Date.UTC(localDate.getFullYear(), localDate.getMonth(), localDate.getDate(), localDate.getHours(), localDate.getMinutes(), localDate.getSeconds())
      return new Date(utcSeconds);
    },
    
    distanceOfTimeInWords: function(fromTime, toTime, includeTime) {
      var delta = parseInt((toTime.getTime() - fromTime.getTime()) / 1000);
      if(delta < 60) {
          return 'less than a minute ago';
      } else if(delta < 120) {
          return 'about a minute ago';
      } else if(delta < (45*60)) {
          return (parseInt(delta / 60)).toString() + ' minutes ago';
      } else if(delta < (120*60)) {
          return 'about an hour ago';
      } else if(delta < (24*60*60)) {
          return 'about ' + (parseInt(delta / 3600)).toString() + ' hours ago';
      } else if(delta < (48*60*60)) {
          return '1 day ago';
      } else {
        var days = (parseInt(delta / 86400)).toString();
        if(days > 5) {
          var fmt  = '%B %d'
          if(toTime.getYear() != fromTime.getYear()) { fmt += ', %Y' }
          if(includeTime) fmt += ' %I:%M %p'
          return $.date.strftime(fromTime, fmt);
        } else {
          return days + " days ago"
        }
      }
    },
    
    addHours: function(date, hrs){
      date.setHours(date.getHours() + hrs);
      return date;
    },
    
    addDays: function(date, days){
      date.setDate(date.getDate() + days);
      return date;
    },
    
    timeAgoInWords: function(utc) {
      var rel = (arguments.length > 1) ? arguments[2] : new Date();
      return $.date.distanceOfTimeInWords(utc, rel, arguments[3]);
    }
  }
  
  function zeropad(str, length) {
    var o = str.toString();
  	while (o.length < length) {
  		o = '0' + o;
  	}
  	return o;
  }
})(jQuery)