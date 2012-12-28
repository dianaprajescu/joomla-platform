## The Twitter Package

### Using the Twitter Package

The Twitter package is designed to be a straightforward interface for working with Twitter. It is based on the REST API. You can find documentation on the API at [https://dev.twitter.com/docs/api](https://dev.twitter.com/docs/api).

#### Instantiating JTwitter

Instantiating JTwitter is easy:

```php
$twitter = new JTwitter;
```

This creates a basic JTwitter object that can be used to access publically available read-only resources on twitter.com, which don't require an active access token.

Sometimes it is necessary to provide an active access token. This can be done by instantiating JTwitterOAuth.

Create a Twitter application at [https://dev.twitter.com/apps](https://dev.twitter.com/apps) in order to request permissions.
Instantiate JTwitterOAuth, passing the JRegistry options needed. By default you have to set and send headers manually in your application, but if you want this to be done automatically you can set JRegistry option 'sendheaders' to true.

```php
$options = new JRegistry;
$options->set('consumer_key', $consumer_key);
$options->set('consumer_secret', $consumer_secret);
$options->set('callback', $callback_url);
$options->set('sendheaders', true);
$oauth = new JTwitterOAuth($options);

$twitter = new JTwitter($oauth);
```

Now you can authenticate and request the user to authorise your application in order to get an access token, but if you already have an access token stored you can set it to the JTwitterOAuth object and if it's still valid your application will use it.

```php
// Set the stored access token.
$oauth->setToken($token);

$access_token = $oauth->authenticate();
```

When calling the authenticate() method, your stored access token will be used only if it's valid, a new one will be created if you don't have an access token or if the stored one is not valid. The method will return a valid access token that's going to be used.

#### Accessing the JTwitter API's objects

The Twitter package covers almost all Resources of the REST API 1.0:
* Block object interacts with Block resources.
* DirectMessages object interacts with Direct Messages resources.
* Favorites object interacts with Favorites resources.
* Friends object interacts with Friends and Followers resources.
* Help object interacts with Help resources.
* Lists object interacts with Lists resources.
* Places object interacts with Places and Geo resources.
* Profile object interacts with some resources from Accounts.
* Search object interacts with Search and Saved Searches resources.
* Statuses object interacts with Timelines and Tweets resources.
* Trends object interacts with Trends resources.
* Users object interacts with Users and Suggested Users resources.

Once a JTwitter object has been created, it is simple to use it to access Twitter:

```php
$users = $twitter->users->getUser($user);
```

This will retrieve extended information of a given user, specified by ID or screen name.

#### More Information

The following resources contain more information:
* [Joomla! API Reference](http://api.joomla.org)
* [Twitter REST API Reference](https://dev.twitter.com/docs/api)
* [Web Application using JTwitter package](https://gist.github.com/3258852)
