const { pool } = require('../db/pool');
const { formatEntry } = require('../helpers/format');

async function findByCustomerId(customerId) {
    const [rows] = await pool.query(
        'SELECT * FROM entries WHERE customer_id = ? ORDER BY id DESC',
        [customerId]
    );
    return rows.map(formatEntry);
}

async function findById(id) {
    const [rows] = await pool.query('SELECT * FROM entries WHERE id = ? LIMIT 1', [id]);
    return formatEntry(rows[0]);
}

async function create(data) {
    const [result] = await pool.query(
        `INSERT INTO entries (user_id, customer_id, description, balance, entry_type, time)
         VALUES (?, ?, ?, ?, ?, ?)`,
        [
            data.userId,
            data.customerId,
            data.description,
            data.balance,
            data.entryType,
            data.time
        ]
    );
    return findById(result.insertId);
}

async function deleteById(id) {
    const existing = await findById(id);
    if (!existing) {
        return null;
    }
    await pool.query('DELETE FROM entries WHERE id = ?', [id]);
    return existing;
}

async function updateById(id, data) {
    await pool.query(
        `UPDATE entries SET description = ?, balance = ?, entry_type = ?, time = ?
         WHERE id = ?`,
        [data.description, data.balance, data.entryType, data.time, id]
    );
    return findById(id);
}

module.exports = {
    findByCustomerId,
    findById,
    create,
    deleteById,
    updateById
};
