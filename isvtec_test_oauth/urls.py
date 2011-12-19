from django.conf.urls.defaults import patterns, include, url

# Uncomment the next two lines to enable the admin:
from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',

    url(r'^$', 'isvtec_test_oauth.views.home', name='home'),
    url(r'^logged-in$', 'isvtec_test_oauth.views.home', name='logged-in'),
    url(r'^login-error$', 'isvtec_test_oauth.views.login_error', name='login_error'),
    url(r'^admin/doc/', include('django.contrib.admindocs.urls')),
    url(r'^admin/', include(admin.site.urls)),
    url(r'', include('social_auth.urls')),
)
