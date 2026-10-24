const jwt = require('jsonwebtoken');

module.exports = function(req, res, err) {
    const token = req.header('authorization');
    if(!token) {
        return res.status(401).send("Access Denied");
    }

    try {
        req.user = jwt.verify(token, process.env.TOKEN_SECRET);
    } catch (e) {
        res.status(400).json({
            error: e,
            message: 'Invalid Token'
        })
    }
}