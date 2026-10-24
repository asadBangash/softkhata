const express = require('express');
const router = require('express-promise-router')();

const User = require('../model/user');

const authController = require('../controller/authController');

router.route('/signup').post(authController.newUser);
router.route('/login').post(authController.loginUser);

module.exports = router;