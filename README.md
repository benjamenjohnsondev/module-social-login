# benjohnsondev/module-social-login

## Installation

```bash
composer require benjohnsondev/module-social-login
bin/magento module:enable BenJohnsonDev_SocialLogin
bin/magento setup:upgrade
```

## Configuration

Each social login provider requires a client ID and secret. These are obtained by registering your application with the provider. The links below will take you to the relevant registration pages.

* [Facebook](https://developers.facebook.com/apps/)
* [Google](https://console.developers.google.com/apis/credentials)
* [LinkedIn](https://www.linkedin.com/developer/apps)
* [Instagram](https://www.instagram.com/developer/clients/manage/)
* [Github](https://docs.github.com/en/apps/oauth-apps)

{{your base url}}/social/account/create/
{{your base url}}/social/account/create

Once you have obtained the client ID and secret, you can configure the module in the Magento admin panel.

## Usage

The module adds a new link to the customer account navigation. This link will take the customer to a page where they can connect their social media accounts.

It's worth mentioning that the instagram API does not allow for the retrieval of the user's email address. This means that the user will have to enter their email address manually when connecting
their Instagram account.

## Why use this module?

The benefit that this module has over others is the extensibility that it provides.

There are no huge templates to override, and no huge classes to extend. The module is designed to be as simple as possible to allow for easy extension by developers.

The module is also designed to be easily extended to support new social media providers.

You can add a new Provider by:

* Following the instructions in [The League of Extraordinary Packages - Oauth2 client documentation](https://oauth2-client.thephpleague.com/providers/implementing/).
* Creating a new entry in the table: `social_login_providers` table with your new Provider class.
* Add the requier configuration fields to the admin panel.

## Security Considerations

The security implementations of this module are based on the Oauth2 protocol. The module uses the email address provided by the social media provider to match the user to an existing Magento account.
If no account is found, a new account is created.

The security mechanism is as follows:

1. The user is redirected to the social media provider's login/authentication page.
2. The user logs in and authorizes the application to access their account.
3. The social media provider redirects the user back to the Magento site with an access token.
4. The module uses the access token to request the user's email address from the social media provider.
5. The module uses the email address to attempt to create an account.
6. If no account is found, the module creates a new account and logs the user in.
7. If an account is found, the module logs the user in.
8. The access token is at no point stored in the Magento database.
    * The access token is used only to request the user's email address, first and last names from the social media provider.

The implementation here is based on a "Separate mechanism" approach.

This means that the social media provider is responsible for authenticating the user, and the Magento site is responsible for authorizing the user.

This shifts the responsibility of user authentication to the social media provider, which is a trusted third party.

The unlinking of social media accounts is also implemented in a secure manner. The module will not allow the user to unlink their last social media account if they have no password set.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Oauth2 client libraries are provided by [The League of Extraordinary Packages](https://oauth2-client.thephpleague.com/usage/). Please refer to their documentation for usage instructions.

Third party providers shoud follow the League's [provider guide](https://oauth2-client.thephpleague.com/providers/league/).

## License

[MIT](https://choosealicense.com/licenses/mit/)

## Todo list:

- [TODO](TODO.md)
