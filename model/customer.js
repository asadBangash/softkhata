const { pool } = require('../db/pool');
const { formatCustomer } = require('../helpers/format');

async function findByUserId(userId) {
    const [rows] = await pool.query(
        'SELECT * FROM customers WHERE user_id = ? ORDER BY id DESC',
        [userId]
    );
    return rows.map(formatCustomer);
}

async function findById(id) {
    const [rows] = await pool.query('SELECT * FROM customers WHERE id = ? LIMIT 1', [id]);
    return formatCustomer(rows[0]);
}

async function create(data) {
    const userId = data.user || data.userId;
    const [result] = await pool.query(
        `INSERT INTO customers (user_id, name, number, last_entry, last_entry_type, total_balance, total_expense, total_income)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
        [
            userId,
            data.name,
            data.number,
            data.lastEntry || '0',
            data.lastEntryType || '',
            data.totalBalance || '0',
            data.totalExpense || '0',
            data.totalIncome || '0'
        ]
    );
    return findById(result.insertId);
}

async function deleteById(id) {
    const existing = await findById(id);
    if (!existing) {
        return null;
    }
    await pool.query('DELETE FROM customers WHERE id = ?', [id]);
    return existing;
}

async function updateById(id, data) {
    await pool.query(
        'UPDATE customers SET name = ?, number = ? WHERE id = ?',
        [data.name, data.number, id]
    );
    return findById(id);
}

async function updateTotals(id, totals) {
    await pool.query(
        `UPDATE customers SET last_entry = ?, last_entry_type = ?, total_balance = ?, total_expense = ?, total_income = ?
         WHERE id = ?`,
        [
            totals.lastEntry,
            totals.lastEntryType,
            totals.totalBalance,
            totals.totalExpense,
            totals.totalIncome,
            id
        ]
    );
    return findById(id);
}

module.exports = {
    findByUserId,
    findById,
    create,
    deleteById,
    updateById,
    updateTotals
};
