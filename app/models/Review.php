<?php
namespace App\Models;

use App\Core\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'product_id', 'rating', 'review'];

    /**
     * Get reviews by product ID with user information
     *
     * @param int $productId
     * @return array
     */
    public function getByProductId($productId)
    {
        $sql = "SELECT r.*, u.first_name, u.last_name, u.email 
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
     * Get rating distribution for a product
     *
     * @param int $productId
     * @return array
     */
    public function getRatingDistribution($productId)
    {
        $sql = "SELECT rating, COUNT(*) as count 
                FROM {$this->table} 
                WHERE product_id = ? 
                GROUP BY rating 
                ORDER BY rating DESC";
        
        $results = $this->db->query($sql)->bind([$productId])->all();
        
        // Initialize all ratings to 0
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        
        // Fill in actual counts
        foreach ($results as $result) {
            $distribution[$result['rating']] = (int)$result['count'];
        }
        
        return $distribution;
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
     * Get user's review for a product
     *
     * @param int $userId
     * @param int $productId
     * @return array|null
     */
    public function getUserReview($userId, $productId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND product_id = ?";
        return $this->db->query($sql)->bind([$userId, $productId])->single();
    }

    /**
     * Create a new review
     *
     * @param array $data
     * @return int|false Returns the last inserted ID on success, false on failure
     * @throws \Exception If validation fails
     */
    public function create($data)
    {
        try {
            // Validate required fields
            if (empty($data['review']) || trim($data['review']) === '') {
                throw new \Exception("Review text is required");
            }

            if (empty($data['rating']) || !is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
                throw new \Exception("Rating must be between 1 and 5");
            }

            if (empty($data['user_id']) || empty($data['product_id'])) {
                throw new \Exception("User ID and Product ID are required");
            }

            // Check if user has already reviewed this product
            if ($this->hasUserReviewed($data['user_id'], $data['product_id'])) {
                throw new \Exception("You have already reviewed this product");
            }

            // Sanitize review text
            $data['review'] = trim($data['review']);
            $data['rating'] = (int)$data['rating'];
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = $data['created_at'];

            $sql = "INSERT INTO {$this->table} (user_id, product_id, rating, review, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $result = $this->db->query($sql)->bind([
                $data['user_id'],
                $data['product_id'],
                $data['rating'],
                $data['review'],
                $data['created_at'],
                $data['updated_at']
            ])->execute();
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log("Review creation failed: " . $e->getMessage());
            throw $e; // Re-throw to be caught by the caller
        }
    }

    /**
     * Update an existing review
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws \Exception If validation fails
     */
    public function update($id, $data)
    {
        // Validate required fields
        if (empty($data['review']) || trim($data['review']) === '') {
            throw new \Exception("Review text is required");
        }

        if (empty($data['rating']) || !is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            throw new \Exception("Rating must be between 1 and 5");
        }

        // Sanitize review text
        $data['review'] = trim($data['review']);
        $data['rating'] = (int)$data['rating'];
        $data['updated_at'] = date('Y-m-d H:i:s');

        $sql = "UPDATE {$this->table} SET rating = ?, review = ?, updated_at = ? WHERE id = ?";
        
        return $this->db->query($sql)->bind([
            $data['rating'],
            $data['review'],
            $data['updated_at'],
            $id
        ])->execute();
    }

    /**
     * Delete a review
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->query($sql)->bind([$id])->execute();
    }

    /**
     * Get recent reviews (for admin dashboard)
     *
     * @param int $limit
     * @return array
     */
    public function getRecentReviews($limit = 10)
    {
        $sql = "SELECT r.*, u.first_name, u.last_name, p.product_name 
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.id
                JOIN products p ON r.product_id = p.id
                ORDER BY r.created_at DESC
                LIMIT ?";
        
        return $this->db->query($sql)->bind([$limit])->all();
    }
}