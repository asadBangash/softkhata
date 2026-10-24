const express = require('express');
const logger = require('morgan');
const bodyParser = require('body-parser');
require('dotenv').config();

const { testConnection } = require('./db/pool');

testConnection().catch((err) => {
    console.error('MySQL connection failed:', err.message);
    process.exit(1);
});

const app = express();

const auth = require('./routes/authRoute');
const customers = require('./routes/customersRoute');
const user = require('./routes/userRoute');
const entry = require('./routes/entryRoute');

app.use(logger('dev'));
app.use(bodyParser.json());
app.use(express.json());

app.use('/api/auth', auth);
app.use('/api/customer', customers);
app.use('/api/user', user);
app.use('/api/entry', entry);

app.use((req, res, next) => {
    const err = new Error('Not Found');
    err.status = 404;
    next(err);
});

app.use((err, req, res, next) => {
    const error = app.get('env') === 'development' ? err : {};
    const status = err.status || 500;

    res.status(status).json({
        error: {
            message: error.message
        }
    });

    console.error(err);
});

const port = process.env.PORT || 5000;
app.listen(port, () => console.log('Server is listening on port ' + port));
