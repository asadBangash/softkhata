const passport = require('passport')
const jwtStrategy = require('passport-jwt').Strategy;
const { ExtractJwt } = require('passport-jwt');
const LocalStrategy = require('passport-local').Strategy;
const { JWT_SECRET } = require("./configuration");
const User = require("./model/user");


//JSOB WEB TOKENS STRATEGY
passport.use(new jwtStrategy({

    jwtFromRequest: ExtractJwt.fromHeader("authorization"),
    secretOrKey: JWT_SECRET
}, async (payload,done) => {

    try {
        // Find the user specified in token
        const user = await User.findById(payload.sub)

        // If user does not exist, handle It
        if(!user) {

            return done(null,false);
        }
        //otherwise return the user
        done(null,user)
    } catch(error) {
        done(error, false);
    }

}));