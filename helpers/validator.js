const Joi = require('@hapi/joi');

const registerValidation = (data) => {
    const schema = {
        name: Joi.string().required(),
        email: Joi.string().required().email(),
        password: Joi.string().required(),
        number: Joi.string().required(),
        businessName: Joi.string().required(),
        businessCategory: Joi.string().required()
    };
    return Joi.validate(data, schema);
}

const loginValidation = (data) => {
    const schema = {
        email: Joi.string().required().email(),
        password: Joi.string().required(),
    };
    return Joi.validate(data, schema);
}

module.exports.registerValidation = registerValidation;
module.exports.loginValidation = loginValidation;