from django.db import models
from django.contrib.auth.models import User

# Create your models here.

class profile(models.Model):
    user = models.ForeignKey(User)
    return_url = models.URLField()
