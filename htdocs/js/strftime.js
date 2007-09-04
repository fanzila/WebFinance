/*
	strftime.js - a javascript implementation of the strftime found in
		 the perl module Time::CTime by David Muir Sharnoff and incorporating
		 derivations from other sources as referenced below.
		 
    Copyright (C) 2004  Jonathan Buhacoff  <jonathan@buhacoff.net>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

	To obtain a copy of the GNU General Public License, visit
	http://www.gnu.org/licenses/gpl.html
	or write to the Free Software Foundation, Inc.
	59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// this javascript library requires the sprintf.js library to be imported as well

	var DoW = new Array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
	var DayOfWeek = new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	var MoY = new Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
	var MonthOfYear = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

	var DaysInYear = new Array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365);
	var DaysInLeapYear = new Array(0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335, 366);
//	var DaysInMonth = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
//	var DaysInLeapYearMonth = new Array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

	//use vars qw($template $sec $min $hour $mday $mon $year $wday $yday $isdst);

	var strftime_conversion = new Object();
	strftime_conversion["%"] = "\"%\"";
	strftime_conversion["a"] = "DoW[the_date.getDay()]";
	strftime_conversion["A"] = "DayOfWeek[the_date.getDay()]";
	strftime_conversion["b"] = "MoY[the_date.getMonth()]";
	strftime_conversion["B"] = "MonthOfYear[the_date.getMonth()]";
	strftime_conversion["c"] = "asctime_n(the_date)";
	strftime_conversion["d"] = "sprintf(\"%02d\", the_date.getDate())";
	strftime_conversion["D"] = "sprintf(\"%02d/%02d/%02d\", the_date.getMonth()+1, the_date.getDate(), the_date.getFullYear() % 100)";
	strftime_conversion["e"] = "sprintf(\"%2d\", the_date.getDate())";
	strftime_conversion["f"] = "fracprintf(\"%3.3f\", the_date.getSeconds())";
	strftime_conversion["F"] = "fracprintf(\"%6.6f\", the_date.getSeconds())";
	strftime_conversion["h"] = "MoY[the_date.getMonth()]"; // yes, same as b
	strftime_conversion["H"] = "sprintf(\"%02d\", the_date.getHours())";
	strftime_conversion["I"] = "sprintf(\"%02d\", the_date.getHours() % 12 || 12)";
	strftime_conversion["j"] = "sprintf(\"%03d\", dayofyear(the_date.getFullYear(),the_date.getMonth(),the_date.getDate()))";
	strftime_conversion["k"] = "sprintf(\"%2d\", the_date.getHours())";
	strftime_conversion["l"] = "sprintf(\"%2d\", the_date.getHours() % 12 || 12)";
	strftime_conversion["m"] = "sprintf(\"%02d\", the_date.getMonth()+1)";
	strftime_conversion["M"] = "sprintf(\"%02d\", the_date.getMinutes())";
	strftime_conversion["n"] = "\"\""; // supposed to be a newline, but \n gives a result of "undefined"... 
	strftime_conversion["o"] = "sprintf(\"%d%s\", the_date.getDate(), ((the_date.getDate() < 20 && the_date.getDate() > 3) ? \"th\" : (the_date.getDate()%10 == 1 ? \"st\" : (the_date.getDate()%10 == 2 ? \"nd\" : (the_date.getDate()%10 == 3 ? \"rd\" : \"th\")))))";
	strftime_conversion["p"] = "the_date.getHours() > 11 ? \"PM\" : \"AM\"";
	strftime_conversion["r"] = "sprintf(\"%02d:%02d:%02d %s\", the_date.getHours() % 12 || 12, the_date.getMinutes(), the_date.getSeconds(), the_date.getHours() > 11 ? \"PM\" : \"AM\" )";
	strftime_conversion["R"] = "sprintf(\"%02d:%02d\", the_date.getHours(), the_date.getMinutes())";
	strftime_conversion["S"] = "sprintf(\"%02d\", the_date.getSeconds())";
	strftime_conversion["t"] = "\"\t\"";
	strftime_conversion["T"] = "sprintf(\"%02d:%02d:%02d\", the_date.getHours(), the_date.getMinutes(), the_date.getSeconds())";
	strftime_conversion["U"] = "wkyr(0, the_date.getDay(), dayofyear(the_date.getFullYear(),the_date.getMonth(),the_date.getDate())-1)",
	strftime_conversion["w"] = "the_date.getDay()";
	strftime_conversion["W"] = "wkyr(1,the_date.getDay(), dayofyear(the_date.getFullYear(),the_date.getMonth(),the_date.getDate())-1)",
	strftime_conversion["x"] = "sprintf(\"%02d/%02d/%02d\", the_date.getMonth()+1, the_date.getDate(), the_date.getFullYear() % 100 )";
	strftime_conversion["X"] = "sprintf(\"%02d:%02d:%02d\", the_date.getHours(), the_date.getMinutes(), the_date.getSeconds())"; // yes, same as T
	strftime_conversion["y"] = "sprintf(\"%02d\", the_date.getFullYear() % 100 )";
	strftime_conversion["Y"] = "the_date.getFullYear()";
	strftime_conversion["Z"] = "\"\""; // not supported yet, need to implement tz2zone	'Z',	sub { &tz2zone(undef,undef,$isdst) }


function asctime_n (the_date) {
	var timezone_offset = "";
	if( the_date.getTimezoneOffset() ) {
		var timezone_hours = Math.floor(the_date.getTimezoneOffset()/60);
		var timezone_sign = ( timezone_hours > 0 ? "+" : "-" );
		timezone_offset = "GMT" + timezone_sign + timezone_hours + " ";
	}
	return sprintf("%s %s %2d %2d:%02d:%02d %s%4d",
		DoW[the_date.getDay()] ,
		MoY[the_date.getMonth()] ,
		the_date.getDate() ,
		the_date.getHours() ,
		the_date.getMinutes() ,
		the_date.getSeconds() ,
		timezone_offset ,
		the_date.getFullYear()
		);
/*
	var the_result = 	DoW[the_date.getDay()] + " " +
						MoY[the_date.getMonth()] + " " +
						the_date.getDate() + " " +
						the_date.getHours() + " " +
						the_date.getMinutes() + " " +
						the_date.getSeconds() + " " +
						the_date.getTimezoneOffset() + " " +
						the_date.getFullYear();
	return the_result;
*/
}

function asctime( the_date ) {
	return asctime_n(the_date) + "\n";
}

function fracprintf( the_template, the_number ) {
	var the_result = sprintf(the_template, the_number - Math.round(the_number));
	while( the_result.charAt(0) == "0" ) {
		the_result = the_result.substring(1);
	}
	return the_result;
}

// if wstart0 == 0 then the week starts on sunday (day 0), if it's 1 then monday (day 1), etc.
function wkyr (wstart0, wday0, yday0 ) {
	wday0 = ( wday0 + 7 - wstart0 ) % 7;
	return Math.round( ( yday0 - wday0 + 13 ) / 7 - 1 );
}

// adapted from http://www.mitre.org/tech/cots/LEAPCALC.html
function leapyear( yyyy ) {
	if (yyyy % 4 != 0)
		return 0;
	else if  (yyyy % 400 == 0)
		return 1;
    else if (yyyy % 100 == 0)
		return 0;
    else
    	return 1;
}


// adapted from Day_of_Year in Date::Pcalc
//  yyyy is 4 digit year,  month0 is 0-indexed month (Jan=0, Feb=1, ...)
//  result is indexed the same as the day (if your day is 0-indexed then dayofyear(yyyy,0,0) is day 0, but if you use 1 for the 1st day then dayofyear(yyyy,0,1) is 1 )
function dayofyear( yyyy, month0, day ) {
	if( leapyear(yyyy) ) {
		return DaysInLeapYear[month0] + day;
	}
	else {
		return DaysInYear[month0] + day;
	}
}

// Version 0.1 adapted from Time::CTime available at search.cpan.org
// the_template is a string containing formatting such as "%e %b '%y"
// the_date is a javascript Date object
// example:  var todays_date = strftime("%A, %B %o, %Y", new Date());
function strftime( the_template, the_date ) {
	var the_result = "";
	var i;
	for(i=0; i<the_template.length; i++) {
		if( the_template.charAt(i) == "%" ) {
			var format_symbol = the_template.charAt(i+1) || null;
			var format_code = strftime_conversion[format_symbol] || "\"\"";
			the_result += eval(format_code);
			i++;
		}
		else {
			the_result += the_template.charAt(i);
		}
	}
	return the_result;
}
