const User = require('../model/user');

module.exports = {
    getProfile: async (req, res) => {
        const userId = req.body.userId;
        const user = await User.findById(userId);

        if (user) {
            return res.status(200).json({
                status: true,
                result: {
                    user
                }
            });
        }

        return res.status(200).json({
            status: true,
            result: {
                message: 'User not found..'
            }
        });
    },

    updateProfile: async (req, res) => {
        const userId = req.body.userId;
        const updatedUser = await User.updateById(userId, {
            name: req.body.name,
            email: req.body.email,
            number: req.body.number,
            businessName: req.body.businessName,
            businessCategory: req.body.businessCategory
        });

        if (updatedUser) {
            return res.status(200).json({
                success: true,
                message: 'Successfully User Updated...',
                user: updatedUser
            });
        }

        return res.status(200).json({
            success: true,
            message: 'Something went wrong'
        });
    }
};
