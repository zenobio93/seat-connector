# Driver Implementation

## Structure

Your driver implementation must meat the minimal structure bellow :

```
/
/Config
/Config/{driver}.config.php
/Config/seat-connector.config.php
/Driver
/Driver/{Driver}Client.php
/Driver/{Driver}User.php
/Driver/{Driver}Set.php
/Http
/Http/routes.php
/Http/Controllers
/Http/Controllers/RegistrationController.php
/Http/Controllers/SettingsController.php
/{Driver}ConnectorServiceProvider.php
```

## Configuration

The file called `seat-connector.config.php` must contain a structure like this :

```
[
    'label'    => {driver label},
    'icon'     => {driver icon},
    'client'   => {driver client},
    'settings' => {driver settings fields},
];
```

Upper attributes functions are described in the table bellow :

| Attribute  | Function                                                                                 |
| ---------- | ---------------------------------------------------------------------------------------- |
| `label`    | It is the value which will be used as display in SeAT. You can use a translation string  |
| `icon`     | Use a Font-Awesome class - it will be used on `identities` page for display purpose only |
| `client`   | Provide the **FQDN** to your `IClient` implementation                                    |
| `settings` | Provide a form definition which will be used to generate the driver settings form        |

The `settings` property myst contain an array of fields definition. At least one field is mandatory.
Field structure must meet the format bellow :

```
[
    'name'  => {field name},
    'label' => {field caption},
    'type'  => {field type},
]
```

| Attribute | Function                                                                                                   |
| --------- | ---------------------------------------------------------------------------------------------------------- |
| `name`    | It will be used as `name` attribute under the generated field                                              |
| `label`   | This must be a valid caption string - it will be used inside a `trans` function during the form generation |
| `type`    | This will determine the type of field which must be generated for the form                                 |

The field name will also be used by the connector to search its existing value inside settings.

**IMPORTANT**
> Your driver must store and read all its settings inside the global setting path `seat-connector.drivers.{driver}` (replace `{driver}` by your driver key).
>
> All your settings will be stored and read as an object.

## Routes

Your driver must register at least the 3 routes bellow. They will be used by the connector in order to proceed settings update and user registration.
Replace `{driver}` by your driver configuration key.

| Method   | Name                                                    | Path                                             |
| -------- | ------------------------------------------------------- | ------------------------------------------------ |
| **GET**  | `seat-connector.drivers.{driver}.registration`          | `/seat-connector/registration/{driver}`          |
| **GET**  | `seat-connector.drivers.{driver}.registration.callback` | `/seat-connector/registration/{driver}/callback` |
| **POST** | `seat-connector.drivers.{driver}.settings`              | `/seat-connector/settings/{driver}`              |

In case the platform for which you want to provide a driver is using OAuth flow, please consider using [Socialite](https://socialiteproviders.netlify.com) as its easy to implement and already provides support for a lot of platforms.

## Classes

### IClient

IClient is the entry point of your driver. It will implement methods which will allow the system to communicate with your platform.

It must contain the following methods :
- getInstance(): IClient
- getUsers(): array
- getSets(): array
- getUser(string id): ?IUser
- getSet(string id): ?ISet

#### getInstance()

This method is used by the connector to access your driver implementation and the platform to which it's connected.
It must return an instance of IClient.

At pattern point of view, this class is a singleton and constructor will never be directly used by the connector itself.

#### GetUsers()

This method must return a list of `IUser` which are user registered on the platform.
Returned users can be active or not.

#### getUser()

This method can return an `IUser` related to a registered user on the platform.
It may return `null` which mean no user related to the `ID` sent in parameter has been found.

#### GetSets()

This method must return a list of `ISet` which are the "thing" you want limit access using connector rules.
It can be either channels, server groups, or anything else.

#### GetSet()

This method can return an `ISet` related to a "thing" you want limit access using connector rules.
It may return `null` which mean no "thing" related to the `ID` sent in parameter has been found.

### ISet

ISet is the representation of your platform user group. It can be a channel, a role, a group or whatever word is used on your platform to call an user pair place.

It must contain the following methods :

- getId(): string
- getName(): string
- getMembers(): array
- addMember(IUser user)
- removeMember(IUser user)

#### getId()

This method is used by the connector to determine the Set identifier on your platform.
To improve flexibility, this method have to return a string value - which can be parse by your driver in order to do its own business logic.

#### getName()

This method is used by the connector to determine the Set name on your platform.

#### getMembers()

This method is used by the connector to list users which are currently in the Set.

#### addMember()

This method is used by the connector to add an user inside the Set.

#### removeMember()

This method is used by the connector to remove an user from the Set.

### IUser

IUser is the representation of your platform physical user. It can be a chatter, speaker, member or whatever word is used on your platform to call a human.

It must contain the following methods :

- getClientId(): string
- getUniqueId(): string
- getName(): string
- setName(string name)
- getSets(): array
- addSet(ISet set)
- removeSet(ISet set)

#### getClientId()

This method is used by the connector to determine the User identifier on your platform.
To improve flexibility, this method have to return a string value - which can be parse by your driver in order to do its own business logic.

#### getUniqueId()

This method is used by the connector to ensure an user is unique across all user *rendez-vous* place on your platform.
To improve flexibility, this method have to return a string value - which can be parse by your driver in order to do its own business logic.

#### getName()

This method is used by the connector to determine the User nickname on your platform.

#### setName()

This method is used by the connector to update the user nickname on your platform according to the SeAT owner policy.

**IMPORTANT**
> You must take care of the nickname limit from your platform while implementing `setName` method.
However, please do not throw an exception as the user will not be able to truncated its character name - neither the SeAT instance owner.

#### getSets()

This method is used by the connector to figure in which set the user is actually.

#### addSet()

This method is used by the connector to grant a new Set to the user.

#### removeSet()

This method is used by the connector to revoke a Set from the user.

## Examples

You can find some implementation example of the new connector on repository listed bellow :

- [Slackbot - Slack Connector Driver](https://github.com/zenobio93/slackbot/tree/seat-connector)
- [Discord - Discord Connector Driver](https://github.com/zenobio93/seat-discord-connector/tree/seat-connector)
- [Teamspeak - Teamspeak Connector Driver](https://github.com/zenobio93/seat-teamspeak/tree/seat-connector)
 