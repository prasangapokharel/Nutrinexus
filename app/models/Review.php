<?php
namespace App\Models;

use App\Core\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'product_id', 'rating', 'review'];

    /**
     * Get reviews by product ID
     *
     * @param int $productId
     * @return array
     */
    public function getByProductId($productId)
    {
        $sql = "SELECT r.*, u.first_name, u.last_name 
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC";
        
        return $this->db->query($sql)->bind([$productId])->all();
    }

    /**
     * Get average rating for a product
     *
     * @param int $productId
     * @return float
     */
    public function getAverageRating($productId)
    {
        $sql = "SELECT AVG(rating) as avg_rating FROM {$this->table} WHERE product_id = ?";
        $result = $this->db->query($sql)->bind([$productId])->single();
        return $result ? round((float)$result['avg_rating'], 1) : 0;
    }

    /**
     * Get review count for a product
     *
     * @param int $productId
     * @return int
     */
    public function getReviewCount($productId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE product_id = ?";
        $result = $this->db->query($sql)->bind([$productId])->single();
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Check if user has reviewed a product
     *
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public function hasUserReviewed($userId, $productId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ? AND product_id = ?";
        $result = $this->db->query($sql)->bind([$userId, $productId])->single();
        return $result && $result['count'] > 0;
    }

    /**
     * Create a new review
     *
     * @param array $data
     * @return int|false Returns the last inserted ID on success, false on failure
     * @throws \Exception If validation fails
     */
    public function create($data): int|false
    {
        // Validate required fields
        if (empty($data['review'])) {
            throw new \Exception("Review is required");
        }

        if (empty($data['rating']) || !is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            throw new \Exception("Rating must be between 1 and 5");
        }

        if (empty($data['user_id']) || empty($data['product_id'])) {
            throw new \Exception("User ID and Product ID are required");
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = $data['created_at'];
        $sql = "INSERT INTO {$this->table} (user_id, product_id, rating, review, created_at, updated_at) 
                VALUES (:user_id, :product_id, :rating, :review, :created_at, :updated_at)";
        
        $query = $this->db->query($sql)
            ->bind([
                ':user_id' => $data['user_id'],
                ':product_id' => $data['product_id'],
                ':rating' => $data['rating'],
                ':review' => $data['review'],
                ':created_at' => $data['created_at'],
                ':updated_at' => $data['updated_at']
            ]);
        
        if ($query->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
}