from django.conf.urls.defaults import patterns, include, url
from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
                       url(r'^$', 'views.profile', name='profile'),
                       url(r'^oauth/', include('oauth_provider.urls')),
                       url(r'^admin/', include(admin.site.urls)),
                       url(r'^accounts/login/$', 'django.contrib.auth.views.login'),
                       url(r'^accounts/profile/$', 'views.profile', name='profile'),
                       url(r'^accounts/', include('registration_backends.isvtec.urls')),
                       url(r'^accounts/verify_credentials.json$', 'views.verify_credentials', name='user_getinfo'),
                       url(r'^users/(?P<email>\w+)$', 'views.registration_info', name='registered'),
)
