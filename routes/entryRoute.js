const router = require('express-promise-router')();

const entryController = require('../controller/entryController');

router.route('/add').post(entryController.addEntry)
router.route('/get').post(entryController.getEntries)
router.route('/details').post(entryController.entryDetails)
router.route('/delete').post(entryController.deleteEntry)
router.route('/update').post(entryController.updateEntry)

module.exports = router;