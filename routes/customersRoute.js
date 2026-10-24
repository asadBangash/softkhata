const express = require('express');
const router = require('express-promise-router')();
const passport = require('passport');

// const router = require('express').Router();
const verify = require('../helpers/verifyToken');

const customersController = require('../controller/customersController');

router.route('/get').post(customersController.getAllCustomers)
router.route('/add').post(customersController.addCustomer)
router.route('/delete').post(customersController.deleteCustomer)
router.route('/edit').post(customersController.editCustomer)

module.exports = router;