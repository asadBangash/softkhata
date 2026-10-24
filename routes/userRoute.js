const router = require('express-promise-router')();

const customersController = require('../controller/userController');

router.route('/get').post(customersController.getProfile)
router.route('/update').post(customersController.updateProfile)

module.exports = router;