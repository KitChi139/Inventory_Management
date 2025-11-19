<?php
class Inventory {
  private $conn;
  private $table = "inventory";

  public $InventoryID;
  public $Quantity;

  public function __construct($db) {
    $this->conn = $db;
  }

  public function reduceStock() {
    $query = "UPDATE {$this->table}
              SET Quantity = Quantity - :sold
              WHERE InventoryID = :InventoryID
              AND Quantity >= :sold"; // Prevent negative stocks

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":sold", $this->Quantity);
    $stmt->bindParam(":InventoryID", $this->InventoryID);

    $stmt->execute();

    // Check if any row was actually updated
    return $stmt->rowCount() > 0;
  }
}
?>