#
# Copyright (C) 2010 Cyril Bouthors <cyril@bouthors.org>
#
# This program is free software: you can redistribute it and/or modify it under
# the terms of the GNU General Public License as published by the Free Software
# Foundation, either version 3 of the License, or (at your option) any later
# version.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along with
# this program. If not, see <http://www.gnu.org/licenses/>.
#

DESTDIR=/usr/local
RSYNC=rsync -avz --del

ALL= html/index.html latex/refman.pdf

all: $(ALL);

html/index.html: ../lib/Webfinance.php
	doxygen doxygen.conf

latex/refman.pdf: html/index.html
	$(MAKE) -C latex
	@echo Documentation is here: file://$$PWD/html/index.html

clean:
	rm -rf html latex

install: all
	mkdir -p $(DESTDIR)/usr/share/doc/webfinance
	$(RSYNC) html $(DESTDIR)/usr/share/doc/webfinance
	cp latex/refman.pdf $(DESTDIR)/usr/share/doc/webfinance
