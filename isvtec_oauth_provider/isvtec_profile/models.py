from django.db import models
from django.contrib.auth.models import User
from tastypie.utils import now
import uuid
import hmac

try:
    from hashlib import sha1
except ImportError:
    import sha
    sha1 = sha.sha

class profile(models.Model):
    user = models.ForeignKey(User)
    return_url = models.URLField()


class ApiKey(models.Model):
    user = models.OneToOneField(User, related_name='api_key')
    key = models.CharField(max_length=256, blank=True, default='')
    created = models.DateTimeField(default=now())

    def __unicode__(self):
        return u"%s for %s" % (self.key, self.user)

    def save(self, *args, **kwargs):
        if not self.key:
            self.key = self.generate_key()

        return super(ApiKey, self).save(*args, **kwargs)

    def generate_key(self):
        # Get a random UUID.
        new_uuid = uuid.uuid4()
        # Hmac that beast.
        return hmac.new(str(new_uuid), digestmod=sha1).hexdigest()


def create_api_key(sender, **kwargs):
    """
      A signal for hooking up automatic ``ApiKey`` creation.
    """
    if kwargs.get('created') is True:
        ApiKey.objects.create(user=kwargs.get('instance'))
