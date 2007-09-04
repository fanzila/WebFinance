/*
	sprintf.js - a javascript implementation of the sprintf found in
		 vsprintf.c of the linux kernel 2.6.5 source code
		 
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

// the following should really be constants
var ZEROPAD = 1;		// pad with zero
var SIGN = 2;			// unsigned or signed long
var PLUS = 4;			// show plus
var SPACE = 8;			// space if plus
var LEFT = 16;			// left justified
var SPECIAL = 32;		// 0x
var LARGE = 64;		// use ABCDEF instead of abcdef

// the following was adapted from string.h
function isdigit(chr) {
	if( (""+chr).length > 0 ) {
		var digits = "0123456789";
		return ( digits.indexOf(chr) > -1 ? true : false );
	}
	return false;
}

// the following was adapted from vsprintf.c
function skip_atoi(str) {
	var i=0;
	var p = 0; 
	var n;
	while( p<str.length && isdigit(str.charAt(p)) ) {
		n = str.charAt(p) - 0;
		i = i*10 + n;
		p++;
	}
	return i;
}


// the following was adapted from vsprintf.c
function number(num, base, size, precision, type) {
	var c;
	var tmp = "";
	var digits;
	var small_digits = "0123456789abcdefghijklmnopqrstuvwxyz";
	var large_digits = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	var i;
	var buf = ""; 

	digits = (type & LARGE) ? large_digits : small_digits;
	if (type & LEFT)
		type &= ~ZEROPAD;
	if (base < 2 || base > 36)
		return 0;
	c = (type & ZEROPAD) ? "0" : " ";
	sign = 0;
	if (type & SIGN) {
		if ( num < 0) {
			sign = "-";
			num = -num;
			size--;
		} else if (type & PLUS) {
			sign = "+";
			size--;
		} else if (type & SPACE) {
			sign = " ";
			size--;
		}
	}
	if (type & SPECIAL) {
		if (base == 16)
			size -= 2;
		else if (base == 8)
			size--;
	}
	i = 0;
	if (num == 0) {
		tmp += "0";
		i++;
	}
	else {
		while (num != 0) {
			var remainder = num % base;
			num = Math.floor(num / base);
			tmp += ""+digits.charAt( remainder );
			i++;
		}
	}
	if (i > precision)
		precision = i;
	size -= precision;
	if (!(type&(ZEROPAD+LEFT))) {
		while(size-->0) {
			buf += " ";
		}
	}
	if (sign) {
		buf += ""+sign;
	}
	if (type & SPECIAL) {
		if (base==8) {
			buf += "0";
		} else if (base==16) {
			buf += "0";
			buf += digits.charAt(33);
		}
	}
	if (!(type & LEFT)) {
		while (size-- > 0) {
			buf += ""+c;
		}
	}
	while (i < precision--) {
		buf += "0";
	}
	while (i-- > 0) {
		buf += ""+tmp.charAt(i);
	}
	while (size-- > 0) {
		buf += " ";
	}
	return buf;
}


// this code adapted from vsprintf.c of the linux kernel 2.6.5 source code
// to do :  find the perl source for sprintf and implement some of the extensions
// there like %f, %e, etc.
function sprintf()
{
	var len;
	var num;
	var i;
	var base;
	var end;
	var c; // the padding character (zero or space) for the current context
	var s;	
	var flags;	 // modifiers to pass to the number() function such as left/right alignment, padding, etc.
	var field_width; // how many character-spaces the current item should take. if it's bigger than this number, it will overflow and take more.  if it takes less, the field will be padded with zeros or spaces depending on the context
	var precision; // relates to the number currently being formatted. the number "5" with precision 1 is "5", precision 2 is "05", etc. if the number's precision is greater than the value of this varriable, it will be printed to the greater precision
	var qualifier;
	var p; // mine. the index in the format string currently being considered;   [  0 , fmt_string.length-1 ]
	var fmt;  // the character in the format string currently being considered;   fmt = fmt_string.charAt(p)
	var buf = "";  // the result accmulates here
	var argptr = 0; // mine.  the index of the next argument to be considered (index 0 is the first argument, which should be the format string, and all the other arguments correspond to the formatting items in that string)
	
	if( arguments.length < 1 ) {
		return "";
	}

	
	var fmt_string = arguments[0];
	argptr++;

	if (fmt_string.length < 1) {
		return "";
	}


	for (p=0; p<fmt_string.length; p++) {
		fmt = fmt_string.charAt(p);
		if (fmt != "%") {
			buf += ""+fmt;
			continue;
		}

		// process flags
		flags = 0;
		var found_a_flag = false;
		do {
			p++;
			fmt = fmt_string.charAt(p);
			switch (fmt) {
				case "-":  flags |= LEFT; found_a_flag = true; break;
				case "+":  flags |= PLUS; found_a_flag = true; break;
				case " ":  flags |= SPACE; found_a_flag = true; break;
				case "#":  flags |= SPECIAL; found_a_flag = true; break;
				case "0":  flags |= ZEROPAD; found_a_flag = true; break;
				default:
					found_a_flag = false; break;
			}
		} while( found_a_flag == true );

		// get field width 
		field_width = -1;
		if (isdigit(fmt)) {
			field_width = skip_atoi( fmt_string.substring(p) );			
			p += (""+field_width).length;
			fmt = fmt_string.charAt(p);
		}
		else if (fmt == "*") {
			p++;
			fmt = fmt_string.charAt(p);
			// it's the next argument 
			field_width = arguments[argptr];
			argptr++;
			if (field_width < 0) {
				field_width = -field_width;
				flags |= LEFT;
			}
		}

		// get the precision 
		precision = -1;
		if (fmt == ".") {
			p++;
			fmt = fmt_string.charAt(p);
			if (isdigit(fmt)) {
				precision = skip_atoi( fmt_string.substring(p) );
				p += (""+precision).length;
				fmt = fmt_string.charAt(p);
			}
			else if (fmt == "*") {
				p++;
				fmt = fmt_string.charAt(p);
				// it's the next argument 
				precision = arguments[argptr];
				argptr++;
			}
			if (precision < 0)
				precision = 0;
		}
		
		// get the conversion qualifier 
		qualifier = -1;
		if (fmt == "h" || fmt == "l" || fmt == "L" ||
		    fmt =="Z" || fmt == "z") {
			qualifier = fmt;
			p++;
			fmt = fmt_string.charAt(p);
			if (qualifier == "l" && fmt == "l") {
				qualifier = "L";
				p++;
				fmt = fmt_string.charAt(p);
			}
		}

		// default base 
		base = 10;

		switch (fmt) {
			case "c":
				if (!(flags & LEFT)) {
					while (--field_width > 0) {
						buf += " ";
					}
				}
				c = arguments[argptr];
				argptr++;			
				buf += (""+c).substring(0,1);
				while (--field_width > 0) {
					buf += " ";
				}
				continue;

			case "s":
				s = arguments[argptr];
				argptr++;
				
				len = Math.max( s.length, precision ); 
				if (!(flags & LEFT)) {
					while (len < field_width--) {
						buf += " ";
					}
				}
				buf += ""+ s.substring(0,len);
				while (len < field_width--) {
					buf += " ";
				}
				continue;

			case "p":
				// instead of outputing the address of the corresponding value in the argument list, we'll just output the value itself 
				if (field_width == -1) {
					field_width = 8; // mucking it.  2*sizeof(void *);
					flags |= ZEROPAD;
				}
				var tmpnum = arguments[argptr];
				argptr++;
				buf += ""+number(tmpnum,
						16, field_width, precision, flags);
				continue;


			case "n":
				// FIXME:
				// What does C99 say about the overflow case here? 
				if (qualifier == "l") {
					arguments[argptr] = buf.length;
				} else if (qualifier == "Z" || qualifier == "z") {
					arguments[argptr] = buf.length;
				} else {
					arguments[argptr] = buf.length;
				}
				continue;

			case "%":
				buf += "%";
				continue;

				// integer number formats - set up the flags and "break" 
			case "o":
				base = 8;
				break;

			case "X":
				flags |= LARGE;
			case "x":
				base = 16;
				break;

			case "d":
			case "i":
				flags |= SIGN;
			case "u":
				break;

			default:
				buf += "%";
				if (fmt) {
					buf += fmt;
				} else {
					p--;
					fmt = fmt_string.charAt(p);
				}
				continue;
		}
		// we only get here if the formatting item is some sort of number (there was not a "continue" statement )
		if (qualifier == "L") {
			num = arguments[argptr];
			argptr++;
		}
		else if (qualifier == "l") {
			num = arguments[argptr]; 
			argptr++;
		} else if (qualifier == "Z" || qualifier == "z") {
			num = arguments[argptr];
			argptr++;
		} else if (qualifier == "h") {
			num = arguments[argptr];
			argptr++;
		} else {
			num = arguments[argptr];
			argptr++;
		}
		buf += ""+number(num, base,
				field_width, precision, flags);
	}
	return buf;
}

