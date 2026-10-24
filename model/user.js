const { pool } = require('../db/pool');
const { formatUser } = require('../helpers/format');

async function findOne({ email }) {
    const [rows] = await pool.query('SELECT * FROM users WHERE email = ? LIMIT 1', [email]);
    return formatUser(rows[0], { includePassword: true });
}

async function findById(id) {
    const [rows] = await pool.query('SELECT * FROM users WHERE id = ? LIMIT 1', [id]);
    return formatUser(rows[0], { includePassword: true });
}

async function create(data) {
    const [result] = await pool.query(
        `INSERT INTO users (name, email, password, number, business_name, business_category)
         VALUES (?, ?, ?, ?, ?, ?)`,
        [
            data.name,
            data.email,
            data.password,
            String(data.number),
            data.businessName,
            data.businessCategory
        ]
    );
    return findById(result.insertId);
}

async function updateById(id, data) {
    await pool.query(
        `UPDATE users SET name = ?, email = ?, number = ?, business_name = ?, business_category = ?
         WHERE id = ?`,
        [
            data.name,
            data.email,
            String(data.number),
            data.businessName,
            data.businessCategory,
            id
        ]
    );
    return findById(id);
}

module.exports = {
    findOne,
    findById,
    create,
    updateById
};
