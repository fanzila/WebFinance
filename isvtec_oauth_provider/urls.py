from django.conf.urls.defaults import patterns, include, url
from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
                       url(r'^oauth/', include('oauth_provider.urls')),
                       url(r'^admin/', include(admin.site.urls)),
                       url(r'^accounts/login/$', 'django.contrib.auth.views.login'),
                       (r'^accounts/', include('registration.backends.simple.urls')),
                       url(r'^account/verify_credentials.json$', 'views.verify_credentials', name='user_getinfo'),                       
)
