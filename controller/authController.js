const User = require('../model/user');
const { registerValidation, loginValidation } = require('../helpers/validator');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');

module.exports = {
    newUser: async (req, res) => {
        const { error } = registerValidation(req.body);
        if (error) {
            return res.status(400).json({
                error: error.details[0].message
            });
        }

        const userExist = await User.findOne({ email: req.body.email });
        if (userExist) {
            return res.status(400).json({
                success: false,
                result: {
                    message: 'User is already exist'
                }
            });
        }

        const salt = await bcrypt.genSalt(10);
        const hashedPassword = await bcrypt.hash(req.body.password, salt);

        try {
            await User.create({
                name: req.body.name,
                email: req.body.email,
                number: req.body.number,
                businessName: req.body.businessName,
                businessCategory: req.body.businessCategory,
                password: hashedPassword
            });
            return res.status(200).json({
                success: true,
                result: {
                    message: 'Successfully User Created'
                }
            });
        } catch (e) {
            return res.status(400).json({
                success: false,
                result: {
                    message: e.message
                }
            });
        }
    },

    loginUser: async (req, res) => {
        const { error } = loginValidation(req.body);
        if (error) {
            return res.status(400).json({
                success: false,
                result: {
                    message: error.details[0].message
                }
            });
        }

        const user = await User.findOne({ email: req.body.email });
        if (!user) {
            return res.status(400).json({
                success: false,
                result: {
                    message: 'Invalid Password or Email'
                }
            });
        }

        const validatePassword = await bcrypt.compare(req.body.password, user.password);
        if (!validatePassword) {
            return res.status(400).json({
                success: false,
                result: {
                    message: 'Invalid Password'
                }
            });
        }

        const token = jwt.sign({ _id: user._id }, process.env.TOKEN_SECRET);
        res.header('auth-token', token);

        const userResponse = { ...user };
        delete userResponse.password;

        return res.status(200).json({
            success: true,
            result: {
                user_token: token,
                user: userResponse
            }
        });
    }
};
