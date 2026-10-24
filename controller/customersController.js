const Customer = require('../model/customer');
const User = require('../model/user');

module.exports = {
    getAllCustomers: async (req, res) => {
        const userId = req.body.userId;
        const customers = await Customer.findByUserId(userId);
        const user = await User.findById(userId);

        return res.status(200).json({
            success: true,
            result: {
                user,
                customers
            }
        });
    },

    addCustomer: async (req, res) => {
        const customer = await Customer.create(req.body);

        return res.status(200).json({
            success: true,
            result: {
                message: 'Customer Added Successfully',
                customer
            }
        });
    },

    deleteCustomer: async (req, res) => {
        const customerId = req.body.customerId;
        const customer = await Customer.deleteById(customerId);

        if (customer) {
            return res.status(200).json({
                success: true,
                result: {
                    message: 'Customer Deleted Successfully'
                }
            });
        }

        return res.status(200).json({
            success: true,
            result: {
                message: 'Customer not found'
            }
        });
    },

    editCustomer: async (req, res) => {
        const customerId = req.body._id;
        const updatedCustomer = await Customer.updateById(customerId, {
            name: req.body.name,
            number: req.body.number
        });

        if (updatedCustomer) {
            return res.status(200).json({
                success: true,
                message: 'Successfully Customer Updated...',
                customer: updatedCustomer
            });
        }

        return res.status(200).json({
            success: true,
            message: 'Something went wrong'
        });
    }
};
