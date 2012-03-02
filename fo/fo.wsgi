import os
import sys
path = '/home/wilane/backups/WebFinance'
if path not in sys.path:
   sys.path.append(path)
   sys.path.append(os.path.join(path, 'fo'))

os.environ['DJANGO_SETTINGS_MODULE'] = 'fo.settings'

import django.core.handlers.wsgi
application = django.core.handlers.wsgi.WSGIHandler()
