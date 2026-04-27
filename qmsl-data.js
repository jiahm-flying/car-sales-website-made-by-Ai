// js/qmsl-data.js — QMSL 共享数据与认证模块
// 所有页面引用此文件即可使用 window.QMSL 进行数据操作
(function () {
    'use strict';

    // ======================== 存储键 ========================
    const STORAGE_KEY_USERS = 'qmsl_users';
    const STORAGE_KEY_CARS = 'qmsl_cars';
    const SESSION_KEY_USER = 'qmsl_currentUser';

    // ======================== 演示用户数据 ========================
    const DEMO_USERS = [
        {
            id: 'u1',
            username: 'alice',
            password: 'password1',
            name: 'Alice Johnson',
            address: '123 Main St, Los Angeles, CA',
            phone: '212-555-0101',
            email: 'alice@example.com'
        },
        {
            id: 'u2',
            username: 'bob',
            password: 'password2',
            name: 'Bob Smith',
            address: '456 Oak Ave, Miami, FL',
            phone: '310-555-0202',
            email: 'bob@example.com'
        },
        {
            id: 'u3',
            username: 'charlie',
            password: 'password3',
            name: 'Charlie Lee',
            address: '789 Pine Rd, Chicago, IL',
            phone: '312-555-0303',
            email: 'charlie@example.com'
        }
    ];

    // ======================== 演示车辆数据 ========================
    const DEMO_CARS = [
        {
            id: 'c1',
            sellerId: 'u1',
            brand: 'BMW',
            model: 'M4 Competition',
            year: 2022,
            price: 65000,
            mileage: 12500,
            color: 'Black Sapphire',
            location: 'Los Angeles, CA',
            transmission: 'Automatic',
            fuelType: 'Gasoline',
            engine: '3.0L Twin-Turbo I6',
            drivetrain: 'RWD',
            vin: 'WBS43AZ0XNCJ12345',
            description: 'Immaculate condition. Low miles, ceramic coating, full service history.',
            phone: '212-555-0101',
            image: 'https://placehold.co/600x400/1a1a1a/ffffff?text=BMW+M4',
            imageLarge: 'https://placehold.co/800x500/1a1a1a/ffffff?text=BMW+M4+Competition'
        },
        {
            id: 'c2',
            sellerId: 'u1',
            brand: 'Mercedes-Benz',
            model: 'C300',
            year: 2020,
            price: 35000,
            mileage: 28000,
            color: 'Polar White',
            location: 'New York, NY',
            transmission: 'Automatic',
            fuelType: 'Gasoline',
            engine: '2.0L Turbo I4',
            drivetrain: 'RWD',
            vin: 'WDDWF8DB2LR567890',
            description: 'Elegant and refined. Premium package, panoramic roof.',
            phone: '212-555-0101',
            image: 'https://placehold.co/600x400/1a1a1a/ffffff?text=MERCEDES+C300',
            imageLarge: 'https://placehold.co/800x500/1a1a1a/ffffff?text=MERCEDES+C300'
        },
        {
            id: 'c3',
            sellerId: 'u2',
            brand: 'Audi',
            model: 'RS5 Sportback',
            year: 2023,
            price: 72000,
            mileage: 5200,
            color: 'Nardo Gray',
            location: 'Miami, FL',
            transmission: 'Automatic',
            fuelType: 'Gasoline',
            engine: '2.9L Twin-Turbo V6',
            drivetrain: 'AWD',
            vin: 'WUAAWDF57PN901234',
            description: 'Nearly new, showroom condition. Sport exhaust, dynamic package.',
            phone: '310-555-0202',
            image: 'https://placehold.co/600x400/1a1a1a/ffffff?text=AUDI+RS5',
            imageLarge: 'https://placehold.co/800x500/1a1a1a/ffffff?text=AUDI+RS5+Sportback'
        },
        {
            id: 'c4',
            sellerId: 'u2',
            brand: 'Porsche',
            model: '911 Carrera',
            year: 2019,
            price: 85000,
            mileage: 18000,
            color: 'GT Silver',
            location: 'Chicago, IL',
            transmission: 'PDK Automatic',
            fuelType: 'Gasoline',
            engine: '3.0L Twin-Turbo Flat-6',
            drivetrain: 'RWD',
            vin: 'WP0AA2A99KS345678',
            description: 'Iconic silhouette, timeless performance. Sport Chrono, adaptive suspension.',
            phone: '310-555-0202',
            image: 'https://placehold.co/600x400/1a1a1a/ffffff?text=PORSCHE+911',
            imageLarge: 'https://placehold.co/800x500/1a1a1a/ffffff?text=PORSCHE+911+Carrera'
        },
        {
            id: 'c5',
            sellerId: 'u3',
            brand: 'Tesla',
            model: 'Model 3 Performance',
            year: 2021,
            price: 42000,
            mileage: 22000,
            color: 'Pearl White',
            location: 'San Francisco, CA',
            transmission: 'Single-Speed',
            fuelType: 'Electric',
            engine: 'Dual Motor AWD',
            drivetrain: 'AWD',
            vin: '5YJ3E1EC8MF987654',
            description: 'Instant torque, zero emissions. Full self-driving capability included.',
            phone: '312-555-0303',
            image: 'https://placehold.co/600x400/1a1a1a/ffffff?text=TESLA+MODEL+3',
            imageLarge: 'https://placehold.co/800x500/1a1a1a/ffffff?text=TESLA+MODEL+3+Perf'
        },
        {
            id: 'c6',
            sellerId: 'u3',
            brand: 'Range Rover',
            model: 'Sport HSE',
            year: 2022,
            price: 55000,
            mileage: 15800,
            color: 'Santorini Black',
            location: 'Dallas, TX',
            transmission: 'Automatic',
            fuelType: 'Gasoline',
            engine: '3.0L Turbo I6 MHEV',
            drivetrain: 'AWD',
            vin: 'SALWV2SV4NA456789',
            description: 'Commanding presence with refined luxury. Air suspension, Meridian audio.',
            phone: '312-555-0303',
            image: 'https://placehold.co/600x400/1a1a1a/ffffff?text=RANGE+ROVER+SPORT',
            imageLarge: 'https://placehold.co/800x500/1a1a1a/ffffff?text=RANGE+ROVER+SPORT'
        },
        {
            id: 'c7',
            sellerId: 'u1',
            brand: 'BMW',
            model: 'X5 xDrive40i',
            year: 2020,
            price: 48000,
            mileage: 32000,
            color: 'Dark Graphite',
            location: 'Seattle, WA',
            transmission: 'Automatic',
            fuelType: 'Gasoline',
            engine: '3.0L Turbo I6',
            drivetrain: 'AWD',
            vin: '5UXCR6C05L9C23456',
            description: 'Versatile luxury SUV. M Sport package, panoramic roof, heated seats.',
            phone: '212-555-0101',
            image: 'https://placehold.co/600x400/1a1a1a/ffffff?text=BMW+X5',
            imageLarge: 'https://placehold.co/800x500/1a1a1a/ffffff?text=BMW+X5+xDrive40i'
        },
        {
            id: 'c8',
            sellerId: 'u2',
            brand: 'Mercedes-Benz',
            model: 'GLE 450',
            year: 2023,
            price: 62000,
            mileage: 8100,
            color: 'Obsidian Black',
            location: 'Phoenix, AZ',
            transmission: 'Automatic',
            fuelType: 'Gasoline',
            engine: '3.0L Turbo I6 EQ Boost',
            drivetrain: 'AWD',
            vin: '4JGFB5KB7PA789012',
            description: 'Modern luxury redefined. MBUX, driver assistance, 4MATIC.',
            phone: '310-555-0202',
            image: 'https://placehold.co/600x400/1a1a1a/ffffff?text=MERCEDES+GLE',
            imageLarge: 'https://placehold.co/800x500/1a1a1a/ffffff?text=MERCEDES+GLE+450'
        }
    ];

    // ======================== 数据初始化 ========================
    function initData() {
        if (!localStorage.getItem(STORAGE_KEY_USERS)) {
            localStorage.setItem(STORAGE_KEY_USERS, JSON.stringify(DEMO_USERS));
        }
        if (!localStorage.getItem(STORAGE_KEY_CARS)) {
            localStorage.setItem(STORAGE_KEY_CARS, JSON.stringify(DEMO_CARS));
        }
    }
    initData();

    // ======================== 辅助函数 ========================
    function getUsers() {
        return JSON.parse(localStorage.getItem(STORAGE_KEY_USERS) || '[]');
    }
    function saveUsers(users) {
        localStorage.setItem(STORAGE_KEY_USERS, JSON.stringify(users));
    }
    function getAllCars() {
        return JSON.parse(localStorage.getItem(STORAGE_KEY_CARS) || '[]');
    }
    function saveCars(cars) {
        localStorage.setItem(STORAGE_KEY_CARS, JSON.stringify(cars));
    }

    // ======================== 公开 API ========================
    window.QMSL = {

        // 初始化或重置数据（调试用，通常不需要调用）
        initData: initData,

        // 注册新用户，成功后自动登录
        register: function (name, address, phone, email, username, password) {
            const users = getUsers();
            if (users.find(u => u.username.toLowerCase() === username.toLowerCase())) {
                return false;
            }
            const newUser = {
                id: 'u' + Date.now(),
                name,
                address,
                phone,
                email,
                username,
                password
            };
            users.push(newUser);
            saveUsers(users);
            sessionStorage.setItem(SESSION_KEY_USER, JSON.stringify(newUser));
            return true;
        },

        // 登录，成功返回 true，失败返回 false
        login: function (username, password) {
            const users = getUsers();
            const user = users.find(
                u => u.username.toLowerCase() === username.toLowerCase() && u.password === password
            );
            if (user) {
                sessionStorage.setItem(SESSION_KEY_USER, JSON.stringify(user));
                return true;
            }
            return false;
        },

        // 退出登录
        logout: function () {
            sessionStorage.removeItem(SESSION_KEY_USER);
        },

        // 获取当前登录用户，未登录返回 null
        getCurrentUser: function () {
            const data = sessionStorage.getItem(SESSION_KEY_USER);
            return data ? JSON.parse(data) : null;
        },

        // 添加车辆 —— 自动绑定当前登录卖家
        // carData 对象需包含：brand, model, year, price, mileage, color, location
        // 可选字段：image, imageLarge, transmission, fuelType, engine, drivetrain, vin, description
        addCar: function (carData) {
            const currentUser = this.getCurrentUser();
            if (!currentUser) {
                throw new Error('You must be logged in to add a vehicle.');
            }
            const cars = getAllCars();
            const newCar = {
                id: 'c' + Date.now(),
                sellerId: currentUser.id,
                brand: carData.brand,
                model: carData.model,
                year: parseInt(carData.year),
                price: parseFloat(carData.price),
                mileage: parseInt(carData.mileage),
                color: carData.color,
                location: carData.location,
                image: carData.image || `https://placehold.co/600x400/1a1a1a/ffffff?text=${encodeURIComponent(carData.brand + '+' + carData.model)}`,
                imageLarge: carData.imageLarge || '',
                transmission: carData.transmission || '',
                fuelType: carData.fuelType || '',
                engine: carData.engine || '',
                drivetrain: carData.drivetrain || '',
                vin: carData.vin || '',
                description: carData.description || '',
                phone: currentUser.phone || ''
            };
            cars.push(newCar);
            saveCars(cars);
            return newCar;
        },

        // 删除指定车辆
        deleteCar: function (carId) {
            const cars = getAllCars();
            const index = cars.findIndex(c => c.id === carId);
            if (index === -1) return false;
            cars.splice(index, 1);
            saveCars(cars);
            return true;
        },

        // 搜索车辆
        searchCars: function (filters = {}) {
            const cars = getAllCars();
            const { keyword, minYear, maxYear, minPrice, maxPrice } = filters;
            return cars.filter(car => {
                if (keyword && keyword.trim()) {
                    const kw = keyword.trim().toLowerCase();
                    const fullName = (car.brand + ' ' + car.model).toLowerCase();
                    if (!car.brand.toLowerCase().includes(kw) &&
                        !car.model.toLowerCase().includes(kw) &&
                        !fullName.includes(kw)) {
                        return false;
                    }
                }
                if (minYear !== undefined && minYear !== null && minYear !== '' && car.year < Number(minYear)) return false;
                if (maxYear !== undefined && maxYear !== null && maxYear !== '' && car.year > Number(maxYear)) return false;
                if (minPrice !== undefined && minPrice !== null && minPrice !== '' && car.price < Number(minPrice)) return false;
                if (maxPrice !== undefined && maxPrice !== null && maxPrice !== '' && car.price > Number(maxPrice)) return false;
                return true;
            });
        },

        // 获取某个卖家的所有车辆
        getCarsBySeller: function (sellerId) {
            return getAllCars().filter(car => car.sellerId === sellerId);
        }
    };

    console.log('QMSL data module initialized. Demo users and cars loaded.');
})();