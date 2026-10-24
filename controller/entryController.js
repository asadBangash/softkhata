const Entry = require('../model/entry');
const { syncCustomerTotalsFromEntries } = require('../helpers/customerTotals');

module.exports = {
    getEntries: async (req, res) => {
        const entries = await Entry.findByCustomerId(req.body.customerId);
        if (entries) {
            return res.status(200).json({
                status: true,
                entries
            });
        }

        return res.status(200).json({
            status: false,
            message: 'Something went wrong'
        });
    },

    addEntry: async (req, res) => {
        const entry = await Entry.create(req.body);

        await syncCustomerTotalsFromEntries(
            req.body.customerId,
            req.body.entryType,
            req.body.balance
        );

        if (entry) {
            return res.status(200).json({
                status: true,
                entry
            });
        }

        return res.status(200).json({
            status: false,
            message: 'Something went wrong'
        });
    },

    deleteEntry: async (req, res) => {
        const id = req.body.entryId;
        const entry = await Entry.deleteById(id);
        if (entry) {
            await syncCustomerTotalsFromEntries(entry.customerId);
            return res.status(200).json({
                status: true,
                message: 'Successfully entry deleted'
            });
        }

        return res.status(200).json({
            status: false,
            message: 'Entry not found'
        });
    },

    updateEntry: async (req, res) => {
        const entryId = req.body._id;
        const updateEntry = await Entry.updateById(entryId, {
            description: req.body.description,
            balance: req.body.balance,
            entryType: req.body.entryType,
            time: req.body.time
        });

        await syncCustomerTotalsFromEntries(
            req.body.customerId,
            req.body.entryType,
            req.body.balance
        );

        if (updateEntry) {
            return res.status(200).json({
                success: true,
                message: 'Successfully Entry Updated...',
                entry: updateEntry
            });
        }

        return res.status(200).json({
            success: true,
            message: 'Something went wrong'
        });
    },

    entryDetails: async (req, res) => {
        const entry = await Entry.findById(req.body.entryId);
        if (entry) {
            return res.status(200).json({
                status: true,
                data: entry
            });
        }

        return res.status(400).json({
            status: false,
            message: 'Something went wrong'
        });
    }
};
