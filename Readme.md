# Larave Crud Genetaor

## 🚀 Installation
- Install package
```
$ composer require kiyora/dashboard-generator
```
- Then you test it
```
php artisan make:crud {name table} {--route-only= : Only Route y/n} {--permission-only= : Only permission y/n} {--relayouts= : relayouts y/n}
```

- delete crud resource
```
php artisan delete:crud {name table}
```

- publishing and custome stub
```
php artisan vendor:publish --provider="KiyoraDashboard\CrudGeneratorServiceProvider"
```

## 🔐 License
Distributed under the MIT License. See [`LICENSE`](https://github.com) for more information.